<?php

declare(strict_types=1);

namespace App\Modules\NdtRequests\Services;

use App\Models\User;
use App\Modules\Audit\Concerns\RecordsAuditLogs;
use App\Modules\Audit\DTO\AuditData;
use App\Modules\NdtRequests\DTO\NdtRequestFormData;
use App\Modules\NdtRequests\DTO\NdtRequestWeldData;
use App\Modules\NdtRequests\Enums\NdtRequestStatus;
use App\Modules\NdtRequests\Models\NdtRequest;
use App\Modules\Objects\Models\NdtObject;
use App\Modules\Welds\Models\Weld;
use Illuminate\Support\Facades\DB;
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
        return $this->createWithWelds(
            NdtRequestFormData::fromArray($data + ['welds' => []]),
            $actor,
            $ipAddress,
            $userAgent,
        );
    }

    public function createWithWelds(NdtRequestFormData $requestData, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): NdtRequest
    {
        return DB::transaction(function () use ($requestData, $actor, $ipAddress, $userAgent): NdtRequest {
            $object = NdtObject::query()->with('organization')->findOrFail($requestData->objectId);
            $organizationId = $object->organization_id ?? $requestData->organizationId;

            $request = NdtRequest::query()->create($this->normalize([
                'request_number' => $requestData->requestNumber,
                'request_date' => $requestData->requestDate,
                'organization_id' => $organizationId,
                'object_id' => $requestData->objectId,
                'title_id' => $requestData->titleId,
                'priority' => $requestData->priority,
                'due_date' => $requestData->dueDate,
                'basis' => $requestData->basis,
                'comment' => $requestData->comment,
                'status' => NdtRequestStatus::Draft->value,
            ]));

            $this->recordStatusHistory($request, null, NdtRequestStatus::Draft, $actor);

            foreach ($requestData->welds as $weldData) {
                $this->attachResolvedWeld($request, $weldData, $actor, $ipAddress, $userAgent);
            }

            $request->loadCount('welds');

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

            return $request;
        });
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
        $before = $this->snapshot($request->loadCount('welds'));
        $request->fill($this->normalize($data))->save();
        $request->refresh();
        $request->loadCount('welds');

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

        $before = $this->snapshot($request->loadCount('welds'));
        $previousStatus = $request->status;
        $request->status = $status;
        $request->save();
        $request->refresh();
        $request->loadCount('welds');

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

    public function attachResolvedWeld(
        NdtRequest $request,
        NdtRequestWeldData $weldData,
        ?User $actor = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): Weld {
        $weld = $this->resolveOrCreateWeld($request->object_id, $weldData, $actor, $ipAddress, $userAgent);

        $this->attachWeld($request, $weld, $actor, $ipAddress, $userAgent);

        return $weld;
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
            'priority' => $request->priority,
            'status' => $request->status->value,
            'welds_count' => $request->welds_count ?? null,
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

    private function resolveOrCreateWeld(
        int $objectId,
        NdtRequestWeldData $weldData,
        ?User $actor = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): Weld {
        $weld = Weld::query()
            ->where('object_id', $objectId)
            ->where('weld_number', $weldData->weldNumber)
            ->first();

        if ($weld !== null) {
            return $weld;
        }

        $weld = Weld::query()->create($this->normalize($weldData->toRequestPayload($objectId) + [
            'status' => 'created',
        ]));

        $this->recordAudit(
            AuditData::forModelChange(
                entityType: Weld::class,
                entityId: $weld->getKey(),
                operation: 'weld.created_from_ndt_request',
                after: [
                    'object_id' => $weld->object_id,
                    'weld_number' => $weld->weld_number,
                ],
                actor: $actor,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            ),
        );

        return $weld;
    }
}
