<?php

declare(strict_types=1);

namespace App\Modules\NdtRequests\Services;

use App\Models\User;
use App\Modules\Audit\Concerns\RecordsAuditLogs;
use App\Modules\Audit\DTO\AuditData;
use App\Modules\NdtRequests\Enums\NdtRequestStatus;
use App\Modules\NdtRequests\Models\NdtRequest;
use App\Modules\Notifications\Services\NotificationService;
use App\Modules\Welds\Models\Weld;
use Illuminate\Validation\ValidationException;

final class NdtRequestService
{
    use RecordsAuditLogs;

    /**
     * @param  array{
     *     request_number: string,
     *     request_date: string,
     *     organization_id?: int|null,
     *     object_id: int,
     *     title_id?: int|null,
     *     priority?: string|null,
     *     due_date?: string|null,
     *     basis?: string|null,
     *     comment?: string|null
     * } $data
     */
    public function create(array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): NdtRequest
    {
        $request = NdtRequest::query()->create($this->normalize($data + ['status' => NdtRequestStatus::Draft->value]));

        $this->recordStatusHistory($request, null, NdtRequestStatus::Draft, $actor);
        $this->recordAudit(
            AuditData::forModelChange(
                entityType: NdtRequest::class,
                entityId: $request->getKey(),
                operation: 'ndt_request.created',
                after: $this->snapshot($request),
                actor: $actor,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            ),
        );

        if ($status === NdtRequestStatus::Clarification) {
            app(NotificationService::class)->notifyRequestClarification($request);
        }

        return $request;
    }

    /**
     * @param  array{
     *     request_number?: string,
     *     request_date?: string,
     *     organization_id?: int|null,
     *     object_id?: int,
     *     title_id?: int|null,
     *     priority?: string|null,
     *     due_date?: string|null,
     *     basis?: string|null,
     *     comment?: string|null
     * } $data
     */
    public function update(NdtRequest $request, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): NdtRequest
    {
        $before = $this->snapshot($request);
        $request->fill($this->normalize($data))->save();
        $request->refresh();

        $this->recordAudit(
            AuditData::forModelChange(
                entityType: NdtRequest::class,
                entityId: $request->getKey(),
                operation: 'ndt_request.updated',
                before: $before,
                after: $this->snapshot($request),
                actor: $actor,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            ),
        );

        return $request;
    }

    public function updateStatus(NdtRequest $request, NdtRequestStatus $status, ?string $comment = null, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): NdtRequest
    {
        if ($status === NdtRequestStatus::InWork && $request->welds()->count() === 0) {
            throw ValidationException::withMessages([
                'status' => 'Нельзя перевести заявку в работу без стыков.',
            ]);
        }

        if ($request->status === $status) {
            return $request;
        }

        $before = $this->snapshot($request);
        $previousStatus = $request->status;
        $request->status = $status;
        $request->save();
        $request->refresh();

        $this->recordStatusHistory($request, $previousStatus, $status, $actor, $comment);
        $this->recordAudit(
            AuditData::forModelChange(
                entityType: NdtRequest::class,
                entityId: $request->getKey(),
                operation: 'ndt_request.status_updated',
                before: $before,
                after: $this->snapshot($request),
                actor: $actor,
                reason: $comment,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            ),
        );

        return $request;
    }

    public function attachWeld(NdtRequest $request, Weld $weld, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): void
    {
        if ($request->object_id !== $weld->object_id) {
            throw ValidationException::withMessages([
                'weld_id' => 'Стык должен относиться к тому же объекту/участку, что и заявка.',
            ]);
        }

        if ($request->welds()->whereKey($weld->getKey())->exists()) {
            return;
        }

        $request->welds()->attach($weld->getKey());

        $this->recordAudit(
            AuditData::forModelChange(
                entityType: NdtRequest::class,
                entityId: $request->getKey(),
                operation: 'ndt_request.weld_attached',
                after: [
                    'ndt_request_id' => $request->getKey(),
                    'weld_id' => $weld->getKey(),
                ],
                actor: $actor,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            ),
        );
    }

    public function detachWeld(NdtRequest $request, Weld $weld, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): void
    {
        $request->welds()->detach($weld->getKey());

        $this->recordAudit(
            AuditData::forModelChange(
                entityType: NdtRequest::class,
                entityId: $request->getKey(),
                operation: 'ndt_request.weld_detached',
                after: [
                    'ndt_request_id' => $request->getKey(),
                    'weld_id' => $weld->getKey(),
                ],
                actor: $actor,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            ),
        );
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
    private function snapshot(NdtRequest $request): array
    {
        return [
            'id' => $request->getKey(),
            'request_number' => $request->request_number,
            'organization_id' => $request->organization_id,
            'object_id' => $request->object_id,
            'title_id' => $request->title_id,
            'status' => $request->status->value,
        ];
    }

    private function recordStatusHistory(NdtRequest $request, NdtRequestStatus|string|null $fromStatus, NdtRequestStatus $toStatus, ?User $actor = null, ?string $comment = null): void
    {
        $request->statusHistory()->create([
            'from_status' => $fromStatus instanceof NdtRequestStatus ? $fromStatus->value : $fromStatus,
            'to_status' => $toStatus->value,
            'changed_by_user_id' => $actor?->getKey(),
            'comment' => $comment,
        ]);
    }
}
