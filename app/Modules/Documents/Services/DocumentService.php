<?php

declare(strict_types=1);

namespace App\Modules\Documents\Services;

use App\Models\User;
use App\Modules\Audit\Concerns\RecordsAuditLogs;
use App\Modules\Audit\DTO\AuditData;
use App\Modules\Documents\Enums\DocumentStatus;
use App\Modules\Documents\Enums\DocumentVersionStatus;
use App\Modules\Documents\Models\Document;
use App\Modules\Documents\Models\DocumentFile;
use App\Modules\Documents\Models\DocumentRelation;
use App\Modules\Documents\Models\DocumentVersion;
use App\Modules\Documents\Models\File;
use App\Modules\Objects\Models\NdtObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

final class DocumentService
{
    use RecordsAuditLogs;

    public function __construct(
        private readonly FileService $fileService,
    ) {}

    /**
     * @param  array{
     *     document_type_id: int,
     *     number?: string|null,
     *     document_date: string,
     *     organization_id?: int|null,
     *     city_id?: int|null,
     *     object_id?: int|null,
     *     employee_id?: int|null,
     *     equipment_id?: int|null,
     *     ndt_request_id?: int|null,
     *     valid_until?: string|null,
     *     status: string,
     *     comment?: string|null
     * }  $data
     */
    public function create(array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): Document
    {
        return DB::transaction(function () use ($data, $actor, $ipAddress, $userAgent): Document {
            $document = Document::query()->create($this->normalize($data, $actor));

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: Document::class,
                    entityId: $document->getKey(),
                    operation: 'document.created',
                    after: $this->snapshot($document->refresh()),
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $document;
        });
    }

    /**
     * @param  array{
     *     document_type_id?: int,
     *     number?: string|null,
     *     document_date?: string,
     *     organization_id?: int|null,
     *     city_id?: int|null,
     *     object_id?: int|null,
     *     employee_id?: int|null,
     *     equipment_id?: int|null,
     *     ndt_request_id?: int|null,
     *     valid_until?: string|null,
     *     status?: string,
     *     comment?: string|null
     * }  $data
     */
    public function update(Document $document, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): Document
    {
        return DB::transaction(function () use ($document, $data, $actor, $ipAddress, $userAgent): Document {
            $before = $this->snapshot($document);
            $document->fill($this->normalize($data, $actor, false))->save();
            $document->refresh();

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: Document::class,
                    entityId: $document->getKey(),
                    operation: 'document.updated',
                    before: $before,
                    after: $this->snapshot($document),
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $document;
        });
    }

    public function attachFile(Document $document, File $file, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): DocumentFile
    {
        return DB::transaction(function () use ($document, $file, $actor, $ipAddress, $userAgent): DocumentFile {
            $documentFile = DocumentFile::query()->firstOrCreate([
                'document_id' => $document->getKey(),
                'file_id' => $file->getKey(),
            ], [
                'attached_by_user_id' => $actor?->getKey(),
            ]);

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: DocumentFile::class,
                    entityId: $documentFile->getKey(),
                    operation: 'document.file.attached',
                    after: [
                        'document_id' => $document->getKey(),
                        'file_id' => $file->getKey(),
                        'attached_by_user_id' => $documentFile->attached_by_user_id,
                    ],
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $documentFile;
        });
    }

    /**
     * @param  array{basis: string}  $data
     */
    public function addVersion(Document $document, UploadedFile $upload, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): DocumentVersion
    {
        return DB::transaction(function () use ($document, $upload, $data, $actor, $ipAddress, $userAgent): DocumentVersion {
            $document->versions()->update([
                'status' => DocumentVersionStatus::Superseded->value,
            ]);

            $file = $this->fileService->store($upload, $actor, $document);
            $versionNumber = (int) ($document->versions()->max('version_number') ?? 0) + 1;

            $version = $document->versions()->create([
                'version_number' => $versionNumber,
                'file_id' => $file->getKey(),
                'created_by_user_id' => $actor?->getKey(),
                'basis' => $data['basis'],
                'status' => DocumentVersionStatus::Current,
            ]);

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: DocumentVersion::class,
                    entityId: $version->getKey(),
                    operation: 'document.version.created',
                    after: [
                        'id' => $version->getKey(),
                        'document_id' => $version->document_id,
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

    public function relate(Document $document, Model $related, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): DocumentRelation
    {
        return DB::transaction(function () use ($document, $related, $actor, $ipAddress, $userAgent): DocumentRelation {
            $relation = DocumentRelation::query()->firstOrCreate([
                'document_id' => $document->getKey(),
                'related_type' => $related::class,
                'related_id' => $related->getKey(),
            ]);

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: DocumentRelation::class,
                    entityId: $relation->getKey(),
                    operation: 'document.relation.created',
                    after: [
                        'document_id' => $relation->document_id,
                        'related_type' => $relation->related_type,
                        'related_id' => $relation->related_id,
                    ],
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $relation;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalize(array $data, ?User $actor = null, bool $creating = true): array
    {
        $objectId = $data['object_id'] ?? null;

        if ($objectId === null && $actor !== null && ! $actor->hasRole('Администратор системы')) {
            $objectId = $actor->objectId();
        }

        $cityId = $data['city_id'] ?? null;
        if ($objectId !== null) {
            $cityId = NdtObject::query()->whereKey($objectId)->value('city_id') ?? $cityId;
        }

        $normalized = [
            'document_type_id' => $data['document_type_id'],
            'number' => $data['number'] ?? null,
            'document_date' => $data['document_date'],
            'organization_id' => $data['organization_id'] ?? null,
            'city_id' => $cityId,
            'object_id' => $objectId,
            'employee_id' => $data['employee_id'] ?? null,
            'equipment_id' => $data['equipment_id'] ?? null,
            'ndt_request_id' => $data['ndt_request_id'] ?? null,
            'valid_until' => $data['valid_until'] ?? null,
            'status' => DocumentStatus::from($data['status']),
            'comment' => $data['comment'] ?? null,
        ];

        if (! $creating) {
            $normalized = array_filter($normalized, static fn (mixed $value): bool => $value !== null);
        }

        return $normalized;
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(Document $document): array
    {
        return [
            'id' => $document->getKey(),
            'document_type_id' => $document->document_type_id,
            'number' => $document->number,
            'document_date' => $document->document_date?->toDateString(),
            'organization_id' => $document->organization_id,
            'city_id' => $document->city_id,
            'object_id' => $document->object_id,
            'employee_id' => $document->employee_id,
            'equipment_id' => $document->equipment_id,
            'ndt_request_id' => $document->ndt_request_id,
            'valid_until' => $document->valid_until?->toDateString(),
            'status' => $document->status->value,
            'comment' => $document->comment,
        ];
    }
}
