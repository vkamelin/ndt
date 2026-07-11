<?php

declare(strict_types=1);

namespace App\Modules\NdtTasks\Services;

use App\Models\User;
use App\Modules\Audit\Concerns\RecordsAuditLogs;
use App\Modules\Audit\DTO\AuditData;
use App\Modules\Employees\Enums\EmployeeStatus;
use App\Modules\Employees\Models\Employee;
use App\Modules\Equipment\Services\QualificationGuardService;
use App\Modules\NdtRequests\Models\NdtRequest;
use App\Modules\NdtTasks\DTO\AssignNdtTaskData;
use App\Modules\NdtTasks\Enums\NdtTaskStatus;
use App\Modules\NdtTasks\Models\NdtTask;
use App\Modules\Welds\Models\Weld;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class NdtTaskService
{
    use RecordsAuditLogs;

    public function __construct(
        private readonly NdtTaskNotificationService $notifications,
        private readonly QualificationGuardService $qualificationGuard,
    ) {}

    /**
     * Create a task, attach weld positions, and assign an executor when provided.
     */
    public function create(AssignNdtTaskData $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): NdtTask
    {
        return DB::transaction(function () use ($data, $actor, $ipAddress, $userAgent): NdtTask {
            $this->assertRequestMatchesObject($data->ndtRequestId, $data->objectId);

            $task = NdtTask::query()->create([
                'task_number' => $data->taskNumber,
                'ndt_request_id' => $data->ndtRequestId,
                'object_id' => $data->objectId,
                'ndt_method_id' => $data->ndtMethodId,
                'assignee_employee_id' => null,
                'planned_date' => $data->plannedDate,
                'priority' => $data->priority,
                'comment' => $data->comment,
                'status' => NdtTaskStatus::Created->value,
            ]);

            $this->recordStatusHistory($task, null, NdtTaskStatus::Created, $actor, $data->comment);
            $this->syncItems($task, $data->weldIds, $actor, $ipAddress, $userAgent);

            if ($data->assigneeEmployeeId !== null) {
                $this->assignEmployee($task, $data->assigneeEmployeeId, $actor, $ipAddress, $userAgent);
            }

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: NdtTask::class,
                    entityId: $task->getKey(),
                    operation: 'ndt_task.created',
                    after: $this->snapshot($task->refresh()),
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $task->refresh();
        });
    }

    /**
     * Update task planning data while the task is still editable.
     */
    public function update(NdtTask $task, AssignNdtTaskData $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): NdtTask
    {
        if (! in_array($task->status, [NdtTaskStatus::Created, NdtTaskStatus::Assigned], true)) {
            throw ValidationException::withMessages([
                'status' => 'Редактирование задания доступно только до начала исполнения.',
            ]);
        }

        return DB::transaction(function () use ($task, $data, $actor, $ipAddress, $userAgent): NdtTask {
            $before = $this->snapshot($task);
            $this->assertRequestMatchesObject($data->ndtRequestId, $data->objectId);

            $task->fill([
                'task_number' => $data->taskNumber,
                'ndt_request_id' => $data->ndtRequestId,
                'object_id' => $data->objectId,
                'ndt_method_id' => $data->ndtMethodId,
                'planned_date' => $data->plannedDate,
                'priority' => $data->priority,
                'comment' => $data->comment,
            ])->save();

            $this->syncItems($task->refresh(), $data->weldIds, $actor, $ipAddress, $userAgent);

            if ($data->assigneeEmployeeId !== null) {
                $this->assignEmployee($task->refresh(), $data->assigneeEmployeeId, $actor, $ipAddress, $userAgent);
            } elseif ($task->assignee_employee_id !== null) {
                $this->clearAssignee($task, $actor, $ipAddress, $userAgent);
            }

            $task->refresh();

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: NdtTask::class,
                    entityId: $task->getKey(),
                    operation: 'ndt_task.updated',
                    before: $before,
                    after: $this->snapshot($task),
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $task;
        });
    }

    /**
     * @param  list<int>  $methodIds
     *                                Synchronize the methods assigned to a weld.
     */
    public function syncWeldMethods(Weld $weld, array $methodIds, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): Weld
    {
        return DB::transaction(function () use ($weld, $methodIds, $actor, $ipAddress, $userAgent): Weld {
            $before = [
                'weld_id' => $weld->getKey(),
                'method_ids' => $weld->ndtMethods()->pluck('ndt_methods.id')->all(),
            ];

            $weld->ndtMethods()->sync($methodIds);
            $weld->refresh();

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: Weld::class,
                    entityId: $weld->getKey(),
                    operation: 'weld.ndt_methods_synced',
                    before: $before,
                    after: [
                        'weld_id' => $weld->getKey(),
                        'method_ids' => $weld->ndtMethods()->pluck('ndt_methods.id')->all(),
                    ],
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $weld;
        });
    }

    /**
     * Move a task to accepted status when the assignee starts work.
     */
    public function accept(NdtTask $task, ?User $actor = null, ?string $comment = null, ?string $ipAddress = null, ?string $userAgent = null): NdtTask
    {
        return $this->transition($task, NdtTaskStatus::Accepted, [NdtTaskStatus::Assigned, NdtTaskStatus::Returned], 'ndt_task.accepted', $actor, $comment, $ipAddress, $userAgent);
    }

    /**
     * Mark the task as actively in progress.
     */
    public function startWork(NdtTask $task, ?User $actor = null, ?string $comment = null, ?string $ipAddress = null, ?string $userAgent = null): NdtTask
    {
        return $this->transition($task, NdtTaskStatus::InWork, [NdtTaskStatus::Accepted], 'ndt_task.started', $actor, $comment, $ipAddress, $userAgent);
    }

    /**
     * Mark the task as completed.
     */
    public function complete(NdtTask $task, ?User $actor = null, ?string $comment = null, ?string $ipAddress = null, ?string $userAgent = null): NdtTask
    {
        return $this->transition($task, NdtTaskStatus::Completed, [NdtTaskStatus::InWork], 'ndt_task.completed', $actor, $comment, $ipAddress, $userAgent);
    }

    /**
     * Mark the task as partially completed.
     */
    public function completePartial(NdtTask $task, ?User $actor = null, ?string $comment = null, ?string $ipAddress = null, ?string $userAgent = null): NdtTask
    {
        return $this->transition($task, NdtTaskStatus::Partial, [NdtTaskStatus::InWork], 'ndt_task.partial', $actor, $comment, $ipAddress, $userAgent);
    }

    /**
     * Return the task back to planning or reassignment.
     */
    public function returnTask(NdtTask $task, ?User $actor = null, ?string $comment = null, ?string $ipAddress = null, ?string $userAgent = null): NdtTask
    {
        return $this->transition($task, NdtTaskStatus::Returned, [NdtTaskStatus::Assigned, NdtTaskStatus::Accepted, NdtTaskStatus::InWork, NdtTaskStatus::Partial], 'ndt_task.returned', $actor, $comment, $ipAddress, $userAgent);
    }

    /**
     * Cancel the task before it reaches a terminal state.
     */
    public function cancel(NdtTask $task, ?User $actor = null, ?string $comment = null, ?string $ipAddress = null, ?string $userAgent = null): NdtTask
    {
        return $this->transition($task, NdtTaskStatus::Cancelled, [NdtTaskStatus::Created, NdtTaskStatus::Assigned, NdtTaskStatus::Accepted, NdtTaskStatus::InWork, NdtTaskStatus::Partial, NdtTaskStatus::Returned], 'ndt_task.cancelled', $actor, $comment, $ipAddress, $userAgent);
    }

    private function transition(
        NdtTask $task,
        NdtTaskStatus $toStatus,
        array $allowedFromStatuses,
        string $operation,
        ?User $actor = null,
        ?string $comment = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): NdtTask {
        if ($task->status === $toStatus) {
            return $task;
        }

        if (! in_array($task->status, $allowedFromStatuses, true)) {
            throw ValidationException::withMessages([
                'status' => 'Нельзя выполнить это действие из текущего статуса задания.',
            ]);
        }

        return DB::transaction(function () use ($task, $toStatus, $operation, $actor, $comment, $ipAddress, $userAgent): NdtTask {
            $before = $this->snapshot($task);
            $previousStatus = $task->status;
            $task->status = $toStatus;
            $task->save();
            $task->refresh();

            $this->recordStatusHistory($task, $previousStatus, $toStatus, $actor, $comment);
            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: NdtTask::class,
                    entityId: $task->getKey(),
                    operation: $operation,
                    before: $before,
                    after: $this->snapshot($task),
                    actor: $actor,
                    reason: $comment,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $task;
        });
    }

    private function assignEmployee(NdtTask $task, int $employeeId, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): void
    {
        $employee = Employee::query()
            ->with(['users'])
            ->findOrFail($employeeId);

        $this->assertAssigneeIsEligible($task, $employee);

        if ($task->assignee_employee_id === $employee->getKey() && $task->status === NdtTaskStatus::Assigned) {
            return;
        }

        $before = $this->snapshot($task);
        $previousStatus = $task->status;
        $task->assignee_employee_id = $employee->getKey();
        $task->status = NdtTaskStatus::Assigned;
        $task->save();
        $task->refresh();

        if ($previousStatus !== NdtTaskStatus::Assigned) {
            $this->recordStatusHistory($task, $previousStatus, NdtTaskStatus::Assigned, $actor, 'Назначен исполнитель');
        }

        $this->recordAudit(
            AuditData::forModelChange(
                entityType: NdtTask::class,
                entityId: $task->getKey(),
                operation: 'ndt_task.assignee_updated',
                before: $before,
                after: $this->snapshot($task),
                actor: $actor,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            ),
        );

        $this->notifications->notifyAssigned($task);
    }

    private function clearAssignee(NdtTask $task, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): void
    {
        $before = $this->snapshot($task);
        $previousStatus = $task->status;

        $task->assignee_employee_id = null;
        $task->status = NdtTaskStatus::Created;
        $task->save();
        $task->refresh();

        if ($previousStatus !== NdtTaskStatus::Created) {
            $this->recordStatusHistory($task, $previousStatus, NdtTaskStatus::Created, $actor, 'Исполнитель снят с задания');
        }

        $this->recordAudit(
            AuditData::forModelChange(
                entityType: NdtTask::class,
                entityId: $task->getKey(),
                operation: 'ndt_task.assignee_cleared',
                before: $before,
                after: $this->snapshot($task),
                actor: $actor,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            ),
        );
    }

    /**
     * @param  list<int>  $weldIds
     */
    private function syncItems(NdtTask $task, array $weldIds, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): void
    {
        $weldIds = array_values(array_unique(array_map(static fn (int $weldId): int => (int) $weldId, $weldIds)));
        $welds = Weld::query()
            ->with(['ndtMethods'])
            ->whereKey($weldIds)
            ->get()
            ->keyBy('id');

        if ($welds->count() !== count($weldIds)) {
            throw ValidationException::withMessages([
                'weld_ids' => 'Один или несколько стыков не найдены.',
            ]);
        }

        foreach ($weldIds as $weldId) {
            $weld = $welds->get($weldId);

            if ($weld === null) {
                continue;
            }

            if ($weld->object_id !== $task->object_id) {
                throw ValidationException::withMessages([
                    'weld_ids' => 'Все стыки задания должны относиться к тому же объекту/участку.',
                ]);
            }

            if (! $weld->ndtMethods->contains('id', $task->ndt_method_id)) {
                throw ValidationException::withMessages([
                    'weld_ids' => 'Метод контроля должен быть назначен каждому выбранному стыку.',
                ]);
            }
        }

        $before = [
            'task_id' => $task->getKey(),
            'weld_ids' => $task->items()->pluck('weld_id')->all(),
        ];

        $task->items()->delete();

        foreach (array_values($weldIds) as $index => $weldId) {
            $task->items()->create([
                'weld_id' => $weldId,
                'position_number' => $index + 1,
            ]);
        }

        $task->refresh();

        $this->recordAudit(
            AuditData::forModelChange(
                entityType: NdtTask::class,
                entityId: $task->getKey(),
                operation: 'ndt_task.items_synced',
                before: $before,
                after: [
                    'task_id' => $task->getKey(),
                    'weld_ids' => $task->items()->orderBy('position_number')->pluck('weld_id')->all(),
                ],
                actor: $actor,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            ),
        );
    }

    private function assertAssigneeIsEligible(NdtTask $task, Employee $employee): void
    {
        if ($employee->status !== EmployeeStatus::Active) {
            throw ValidationException::withMessages([
                'assignee_employee_id' => 'Исполнитель должен быть активным сотрудником.',
            ]);
        }

        if ($employee->object_id !== $task->object_id) {
            throw ValidationException::withMessages([
                'assignee_employee_id' => 'Исполнитель должен быть закреплен за тем же объектом/участком.',
            ]);
        }

        $hasAssignableUser = $employee->users->contains(
            static function (User $user): bool {
                return $user->isActive() && (
                    $user->hasRole('Дефектоскопист') ||
                    $user->hasRole('Инженер НК / Дешифровщик')
                );
            },
        );

        if (! $hasAssignableUser) {
            throw ValidationException::withMessages([
                'assignee_employee_id' => 'У сотрудника должен быть активный пользователь с ролью исполнителя контроля.',
            ]);
        }

        $task->loadMissing('method');

        if ($task->method?->code !== null) {
            $this->qualificationGuard->ensureQualified($employee, $task->method->code);
        }
    }

    private function assertRequestMatchesObject(int $ndtRequestId, int $objectId): void
    {
        $request = NdtRequest::query()->findOrFail($ndtRequestId);

        if ($request->object_id !== $objectId) {
            throw ValidationException::withMessages([
                'ndt_request_id' => 'Заявка должна относиться к тому же объекту/участку, что и задание.',
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(NdtTask $task): array
    {
        return [
            'id' => $task->getKey(),
            'task_number' => $task->task_number,
            'ndt_request_id' => $task->ndt_request_id,
            'object_id' => $task->object_id,
            'ndt_method_id' => $task->ndt_method_id,
            'assignee_employee_id' => $task->assignee_employee_id,
            'planned_date' => $task->planned_date?->toDateString(),
            'priority' => $task->priority,
            'status' => $task->status->value,
            'weld_ids' => $task->items()->orderBy('position_number')->pluck('weld_id')->all(),
        ];
    }

    private function recordStatusHistory(NdtTask $task, NdtTaskStatus|string|null $fromStatus, NdtTaskStatus $toStatus, ?User $actor = null, ?string $comment = null): void
    {
        $task->statusHistory()->create([
            'from_status' => $fromStatus instanceof NdtTaskStatus ? $fromStatus->value : $fromStatus,
            'to_status' => $toStatus->value,
            'changed_by_user_id' => $actor?->getKey(),
            'comment' => $comment,
        ]);
    }
}
