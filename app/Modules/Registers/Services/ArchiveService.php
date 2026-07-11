<?php

declare(strict_types=1);

namespace App\Modules\Registers\Services;

use App\Models\User;
use App\Modules\Audit\Concerns\RecordsAuditLogs;
use App\Modules\Audit\DTO\AuditData;
use App\Modules\Registers\Models\ArchiveCase;
use App\Modules\Registers\Models\ArchiveCaseItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ArchiveService
{
    use RecordsAuditLogs;

    /**
     * @param  array{
     *     transfer_register_id?: int|null,
     *     number: string,
     *     date: string,
     *     city_id: int,
     *     object_id: int,
     *     comment?: string|null
     * }  $data
     */
    public function create(array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): ArchiveCase
    {
        return DB::transaction(function () use ($data, $actor, $ipAddress, $userAgent): ArchiveCase {
            $archiveCase = ArchiveCase::query()->create([
                'transfer_register_id' => $data['transfer_register_id'] ?? null,
                'number' => $data['number'],
                'date' => $data['date'],
                'city_id' => $data['city_id'],
                'object_id' => $data['object_id'],
                'comment' => $data['comment'] ?? null,
            ]);

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: ArchiveCase::class,
                    entityId: $archiveCase->getKey(),
                    operation: 'archive_case.created',
                    after: $this->snapshot($archiveCase->refresh()),
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $archiveCase;
        });
    }

    /**
     * @param  array{
     *     related_type: string,
     *     related_id: int,
     *     file_id?: int|null,
     *     sort_order?: int|null,
     *     comment?: string|null
     * }  $data
     */
    public function addItem(ArchiveCase $archiveCase, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): ArchiveCaseItem
    {
        return DB::transaction(function () use ($archiveCase, $data, $actor, $ipAddress, $userAgent): ArchiveCaseItem {
            $this->ensureRelatedModelExists($data['related_type'], (int) $data['related_id']);

            $existing = $archiveCase->items()
                ->where('related_type', $data['related_type'])
                ->where('related_id', $data['related_id'])
                ->first();

            if ($existing !== null) {
                return $existing;
            }

            $item = $archiveCase->items()->create([
                'related_type' => $data['related_type'],
                'related_id' => $data['related_id'],
                'file_id' => $data['file_id'] ?? null,
                'sort_order' => $data['sort_order'] ?? 1,
                'comment' => $data['comment'] ?? null,
            ]);

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: ArchiveCaseItem::class,
                    entityId: $item->getKey(),
                    operation: 'archive_case.item.created',
                    after: [
                        'id' => $item->getKey(),
                        'archive_case_id' => $item->archive_case_id,
                        'related_type' => $item->related_type,
                        'related_id' => $item->related_id,
                        'file_id' => $item->file_id,
                        'sort_order' => $item->sort_order,
                        'comment' => $item->comment,
                    ],
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $item;
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(ArchiveCase $archiveCase): array
    {
        return [
            'id' => $archiveCase->getKey(),
            'transfer_register_id' => $archiveCase->transfer_register_id,
            'number' => $archiveCase->number,
            'date' => $archiveCase->date?->toDateString(),
            'city_id' => $archiveCase->city_id,
            'object_id' => $archiveCase->object_id,
            'comment' => $archiveCase->comment,
        ];
    }

    private function ensureRelatedModelExists(string $relatedType, int $relatedId): void
    {
        if (! class_exists($relatedType)) {
            throw ValidationException::withMessages([
                'related_type' => 'Связанная сущность не найдена.',
            ]);
        }

        if (! is_subclass_of($relatedType, Model::class)) {
            throw ValidationException::withMessages([
                'related_type' => 'Связанная сущность не поддерживается.',
            ]);
        }

        if ($relatedType::query()->whereKey($relatedId)->doesntExist()) {
            throw ValidationException::withMessages([
                'related_id' => 'Связанная сущность не найдена.',
            ]);
        }
    }
}
