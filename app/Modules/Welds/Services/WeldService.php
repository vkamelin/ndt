<?php

declare(strict_types=1);

namespace App\Modules\Welds\Services;

use App\Models\User;
use App\Modules\Audit\Concerns\RecordsAuditLogs;
use App\Modules\Audit\DTO\AuditData;
use App\Modules\Welds\Enums\WeldStatus;
use App\Modules\Welds\Models\Weld;

final class WeldService
{
    use RecordsAuditLogs;

    /**
     * @param  array{
     *     object_id: int,
     *     title_id?: int|null,
     *     drawing_id?: int|null,
     *     line_id?: int|null,
     *     weld_number: string,
     *     diameter?: numeric-string|int|float|null,
     *     thickness?: numeric-string|int|float|null,
     *     material_1_id?: int|null,
     *     material_2_id?: int|null,
     *     welded_at?: string|null,
     *     welding_process_id?: int|null,
     *     weld_type_id?: int|null,
     *     pipeline_category_id?: int|null,
     *     medium_id?: int|null,
     *     pwht?: bool,
     *     normative_document_id?: int|null
     * } $data
     */
    public function create(array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): Weld
    {
        $weld = Weld::query()->create($this->normalize($data + ['status' => WeldStatus::Created->value]));

        $this->recordStatusHistory($weld, null, WeldStatus::Created, $actor);
        $this->recordAudit(
            AuditData::forModelChange(
                entityType: Weld::class,
                entityId: $weld->getKey(),
                operation: 'weld.created',
                after: $this->snapshot($weld),
                actor: $actor,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            ),
        );

        return $weld;
    }

    /**
     * @param  array{
     *     object_id?: int,
     *     title_id?: int|null,
     *     drawing_id?: int|null,
     *     line_id?: int|null,
     *     weld_number?: string,
     *     diameter?: numeric-string|int|float|null,
     *     thickness?: numeric-string|int|float|null,
     *     material_1_id?: int|null,
     *     material_2_id?: int|null,
     *     welded_at?: string|null,
     *     welding_process_id?: int|null,
     *     weld_type_id?: int|null,
     *     pipeline_category_id?: int|null,
     *     medium_id?: int|null,
     *     pwht?: bool,
     *     normative_document_id?: int|null
     * } $data
     */
    public function update(Weld $weld, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): Weld
    {
        $before = $this->snapshot($weld);
        $weld->fill($this->normalize($data))->save();
        $weld->refresh();

        $this->recordAudit(
            AuditData::forModelChange(
                entityType: Weld::class,
                entityId: $weld->getKey(),
                operation: 'weld.updated',
                before: $before,
                after: $this->snapshot($weld),
                actor: $actor,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            ),
        );

        return $weld;
    }

    public function updateStatus(Weld $weld, WeldStatus $status, ?string $comment = null, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): Weld
    {
        if ($weld->status === $status) {
            return $weld;
        }

        $before = $this->snapshot($weld);
        $previousStatus = $weld->status;
        $weld->status = $status;
        $weld->save();
        $weld->refresh();

        $this->recordStatusHistory($weld, $previousStatus, $status, $actor, $comment);
        $this->recordAudit(
            AuditData::forModelChange(
                entityType: Weld::class,
                entityId: $weld->getKey(),
                operation: 'weld.status_updated',
                before: $before,
                after: $this->snapshot($weld),
                actor: $actor,
                reason: $comment,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            ),
        );

        return $weld;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalize(array $data): array
    {
        return array_filter($data, static fn (mixed $value): bool => $value !== null);
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(Weld $weld): array
    {
        return [
            'id' => $weld->getKey(),
            'object_id' => $weld->object_id,
            'title_id' => $weld->title_id,
            'drawing_id' => $weld->drawing_id,
            'line_id' => $weld->line_id,
            'weld_number' => $weld->weld_number,
            'status' => $weld->status->value,
        ];
    }

    private function recordStatusHistory(Weld $weld, WeldStatus|string|null $fromStatus, WeldStatus $toStatus, ?User $actor = null, ?string $comment = null): void
    {
        $weld->statusHistory()->create([
            'from_status' => $fromStatus instanceof WeldStatus ? $fromStatus->value : $fromStatus,
            'to_status' => $toStatus->value,
            'changed_by_user_id' => $actor?->getKey(),
            'comment' => $comment,
        ]);
    }
}
