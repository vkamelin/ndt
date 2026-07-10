<?php

declare(strict_types=1);

namespace App\Modules\NdtTasks\DTO;

/**
 * Structured payload for task creation and planning updates.
 */
final class AssignNdtTaskData
{
    /**
     * @param  list<int>  $weldIds
     */
    public function __construct(
        public readonly string $taskNumber,
        public readonly int $ndtRequestId,
        public readonly int $objectId,
        public readonly int $ndtMethodId,
        public readonly ?int $assigneeEmployeeId,
        public readonly string $plannedDate,
        public readonly ?string $priority,
        public readonly ?string $comment,
        public readonly array $weldIds,
    ) {
    }

    /**
     * @param  array{
     *     task_number: string,
     *     ndt_request_id: int,
     *     object_id: int,
     *     ndt_method_id: int,
     *     assignee_employee_id?: int|null,
     *     planned_date: string,
     *     priority?: string|null,
     *     comment?: string|null,
     *     weld_ids: array<int, int|string>
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            taskNumber: $data['task_number'],
            ndtRequestId: $data['ndt_request_id'],
            objectId: $data['object_id'],
            ndtMethodId: $data['ndt_method_id'],
            assigneeEmployeeId: $data['assignee_employee_id'] ?? null,
            plannedDate: $data['planned_date'],
            priority: $data['priority'] ?? null,
            comment: $data['comment'] ?? null,
            weldIds: array_values(array_map(
                static fn (int|string $weldId): int => (int) $weldId,
                $data['weld_ids'],
            )),
        );
    }
}
