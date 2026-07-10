<?php

declare(strict_types=1);

namespace App\Modules\Conclusions\Services;

use App\Models\User;
use App\Modules\Audit\Concerns\RecordsAuditLogs;
use App\Modules\Audit\DTO\AuditData;
use App\Modules\Conclusions\Enums\ConclusionVersionStatus;
use App\Modules\Conclusions\Models\Conclusion;
use App\Modules\Conclusions\Models\ConclusionVersion;
use App\Modules\Documents\Enums\FileStatus;
use App\Modules\Documents\Models\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

final class ConclusionVersionService
{
    use RecordsAuditLogs;

    /**
     * Create a new version and store the generated PDF as a private file.
     */
    public function createVersion(Conclusion $conclusion, string $basis, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): ConclusionVersion
    {
        return DB::transaction(function () use ($conclusion, $basis, $actor, $ipAddress, $userAgent): ConclusionVersion {
            $conclusion->loadMissing(['object.city', 'method', 'request', 'preparedBy', 'checkedBy', 'approvedBy', 'items.result.weld']);
            $versionNumber = (int) ($conclusion->versions()->max('version_number') ?? 0) + 1;
            $pdfContent = $this->buildPdfBytes($conclusion, $versionNumber, $basis);
            $file = $this->storePdfFile($conclusion, $pdfContent, $actor);

            $conclusion->versions()
                ->where('status', ConclusionVersionStatus::Current->value)
                ->update([
                    'status' => ConclusionVersionStatus::Superseded->value,
                ]);

            $version = $conclusion->versions()->create([
                'version_number' => $versionNumber,
                'file_id' => $file->getKey(),
                'created_by_user_id' => $actor?->getKey(),
                'basis' => $basis,
                'status' => ConclusionVersionStatus::Current->value,
            ]);

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: ConclusionVersion::class,
                    entityId: $version->getKey(),
                    operation: 'conclusion.version.created',
                    after: [
                        'id' => $version->getKey(),
                        'conclusion_id' => $version->conclusion_id,
                        'version_number' => $version->version_number,
                        'file_id' => $version->file_id,
                        'created_by_user_id' => $version->created_by_user_id,
                        'basis' => $version->basis,
                        'status' => $version->status->value,
                    ],
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $version;
        });
    }

    public function cancelCurrentVersions(Conclusion $conclusion, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): void
    {
        DB::transaction(function () use ($conclusion, $actor, $ipAddress, $userAgent): void {
            $currentVersions = $conclusion->versions()->where('status', ConclusionVersionStatus::Current->value)->get();

            foreach ($currentVersions as $version) {
                $before = [
                    'id' => $version->getKey(),
                    'status' => $version->status->value,
                ];

                $version->forceFill([
                    'status' => ConclusionVersionStatus::Cancelled->value,
                ])->save();

                $this->recordAudit(
                    AuditData::forModelChange(
                        entityType: ConclusionVersion::class,
                        entityId: $version->getKey(),
                        operation: 'conclusion.version.cancelled',
                        before: $before,
                        after: [
                            'id' => $version->getKey(),
                            'status' => $version->status->value,
                        ],
                        actor: $actor,
                        ipAddress: $ipAddress,
                        userAgent: $userAgent,
                    ),
                );
            }
        });
    }

    public function supersedeCurrentVersions(Conclusion $conclusion, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): void
    {
        DB::transaction(function () use ($conclusion, $actor, $ipAddress, $userAgent): void {
            $currentVersions = $conclusion->versions()->where('status', ConclusionVersionStatus::Current->value)->get();

            foreach ($currentVersions as $version) {
                $before = [
                    'id' => $version->getKey(),
                    'status' => $version->status->value,
                ];

                $version->forceFill([
                    'status' => ConclusionVersionStatus::Superseded->value,
                ])->save();

                $this->recordAudit(
                    AuditData::forModelChange(
                        entityType: ConclusionVersion::class,
                        entityId: $version->getKey(),
                        operation: 'conclusion.version.superseded',
                        before: $before,
                        after: [
                            'id' => $version->getKey(),
                            'status' => $version->status->value,
                        ],
                        actor: $actor,
                        ipAddress: $ipAddress,
                        userAgent: $userAgent,
                    ),
                );
            }
        });
    }

    /**
     * @return string Raw PDF bytes.
     */
    private function buildPdfBytes(Conclusion $conclusion, int $versionNumber, string $basis): string
    {
        $lines = $this->pdfLines($conclusion, $versionNumber, $basis);
        $escapedLines = array_map([$this, 'escapePdfText'], $lines);
        $commands = [
            'BT',
            '/F1 11 Tf',
            '50 800 Td',
        ];

        foreach ($escapedLines as $index => $line) {
            if ($index > 0) {
                $commands[] = 'T*';
            }

            $commands[] = sprintf('(%s) Tj', $line);
        }

        $commands[] = 'ET';
        $contentStream = implode("\n", $commands);

        $objects = [];
        $objects[] = "<< /Type /Catalog /Pages 2 0 R >>";
        $objects[] = "<< /Type /Pages /Kids [3 0 R] /Count 1 >>";
        $objects[] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>";
        $objects[] = "<< /Length ".strlen($contentStream)." >>\nstream\n{$contentStream}\nendstream";
        $objects[] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1).' 0 obj'."\n".$object."\nendobj\n";
        }

        $xrefPosition = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n";
        $pdf .= sprintf("%010d 65535 f \n", 0);

        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        $pdf .= "trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefPosition}\n%%EOF";

        return $pdf;
    }

    /**
     * @return list<string>
     */
    private function pdfLines(Conclusion $conclusion, int $versionNumber, string $basis): array
    {
        $lines = [
            'Conclusion #'.$conclusion->number,
            'Date: '.$conclusion->date?->toDateString(),
            'Object ID: '.$conclusion->object_id,
            'Method ID: '.$conclusion->ndt_method_id,
            'Request ID: '.($conclusion->ndt_request_id ?? 'n/a'),
            'Status: '.$conclusion->status->value,
            'Version: '.$versionNumber,
            'Basis: '.$basis,
            'Items:',
        ];

        foreach ($conclusion->items as $item) {
            $result = $item->result;
            $lines[] = sprintf(
                '%d. Result %d, weld %s, result status %s',
                $item->sort_order,
                $result?->getKey() ?? 0,
                $result?->weld?->weld_number ?? 'n/a',
                $result?->status->value ?? 'n/a',
            );
        }

        return $lines;
    }

    private function storePdfFile(Conclusion $conclusion, string $content, ?User $actor = null): File
    {
        $disk = config('filesystems.default', 'private');
        $storageName = (string) Str::uuid().'.pdf';
        $storagePath = 'conclusions/'.now()->format('Y/m/d').'/'.$storageName;
        $hash = hash('sha256', $content);

        if (Storage::disk($disk)->put($storagePath, $content) !== true) {
            throw new RuntimeException('Unable to store conclusion PDF.');
        }

        $file = File::query()->create([
            'original_name' => $conclusion->number.'.pdf',
            'storage_name' => $storageName,
            'storage_path' => $storagePath,
            'disk' => $disk,
            'mime_type' => 'application/pdf',
            'size' => strlen($content),
            'hash' => $hash,
            'uploaded_by_user_id' => $actor?->getKey(),
            'related_type' => $conclusion::class,
            'related_id' => $conclusion->getKey(),
            'status' => FileStatus::Active->value,
        ]);

        $this->recordAudit(
            AuditData::forModelChange(
                entityType: File::class,
                entityId: $file->getKey(),
                operation: 'file.created',
                after: [
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
                ],
                actor: $actor,
            ),
        );

        return $file;
    }

    private function escapePdfText(string $text): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }
}
