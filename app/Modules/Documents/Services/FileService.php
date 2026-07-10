<?php

declare(strict_types=1);

namespace App\Modules\Documents\Services;

use App\Models\User;
use App\Modules\Audit\Concerns\RecordsAuditLogs;
use App\Modules\Audit\DTO\AuditData;
use App\Modules\Documents\Enums\FileStatus;
use App\Modules\Documents\Models\Document;
use App\Modules\Documents\Models\File;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class FileService
{
    use RecordsAuditLogs;

    /**
     * @param  Model|Document|null  $related
     */
    public function store(UploadedFile $upload, ?User $actor = null, Model|Document|null $related = null): File
    {
        return DB::transaction(function () use ($upload, $actor, $related): File {
            $disk = config('filesystems.default', 'private');
            $storageName = $this->storageName($upload);
            $storagePath = $this->storagePath($storageName);
            $hash = hash_file('sha256', $upload->getRealPath()) ?: throw new RuntimeException('Unable to calculate file hash.');

            Storage::disk($disk)->putFileAs(dirname($storagePath), $upload, basename($storagePath));

            $file = File::query()->create([
                'original_name' => $upload->getClientOriginalName(),
                'storage_name' => $storageName,
                'storage_path' => $storagePath,
                'disk' => $disk,
                'mime_type' => $upload->getMimeType() ?? $upload->getClientMimeType() ?? 'application/octet-stream',
                'size' => $upload->getSize() ?? 0,
                'hash' => $hash,
                'uploaded_by_user_id' => $actor?->getKey(),
                'related_type' => $related === null ? null : $related::class,
                'related_id' => $related?->getKey(),
                'status' => FileStatus::Active,
            ]);

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: File::class,
                    entityId: $file->getKey(),
                    operation: 'file.created',
                    after: $this->snapshot($file),
                    actor: $actor,
                ),
            );

            return $file;
        });
    }

    public function download(File $file): StreamedResponse
    {
        return Storage::disk($file->disk)->download($file->storage_path, $file->original_name, [
            'Content-Type' => $file->mime_type,
        ]);
    }

    public function annul(File $file, ?User $actor = null, ?string $reason = null, ?string $ipAddress = null, ?string $userAgent = null): File
    {
        if ($file->status === FileStatus::Deleted || $file->trashed()) {
            return $file;
        }

        return DB::transaction(function () use ($file, $actor, $reason, $ipAddress, $userAgent): File {
            $before = $this->snapshot($file);
            $file->fill([
                'status' => FileStatus::Deleted,
            ])->save();
            $file->delete();

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: File::class,
                    entityId: $file->getKey(),
                    operation: 'file.deleted',
                    before: $before,
                    after: $this->snapshot($file->refresh()),
                    actor: $actor,
                    reason: $reason,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $file;
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(File $file): array
    {
        return [
            'id' => $file->getKey(),
            'original_name' => $file->original_name,
            'storage_name' => $file->storage_name,
            'storage_path' => $file->storage_path,
            'disk' => $file->disk,
            'mime_type' => $file->mime_type,
            'size' => $file->size,
            'hash' => $file->hash,
            'uploaded_by_user_id' => $file->uploaded_by_user_id,
            'related_type' => $file->related_type,
            'related_id' => $file->related_id,
            'status' => $file->status->value,
            'deleted_at' => $file->deleted_at?->toAtomString(),
        ];
    }

    private function storageName(UploadedFile $upload): string
    {
        $extension = $upload->getClientOriginalExtension();
        $uuid = Str::uuid()->toString();

        return $extension === '' ? $uuid : $uuid.'.'.$extension;
    }

    private function storagePath(string $storageName): string
    {
        return 'documents/'.now()->format('Y/m/d').'/'.$storageName;
    }
}
