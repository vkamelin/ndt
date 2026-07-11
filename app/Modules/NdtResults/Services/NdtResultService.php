<?php

declare(strict_types=1);

namespace App\Modules\NdtResults\Services;

use App\Models\User;
use App\Modules\Audit\Concerns\RecordsAuditLogs;
use App\Modules\Audit\DTO\AuditData;
use App\Modules\Employees\Models\Employee;
use App\Modules\Equipment\Services\QualificationGuardService;
use App\Modules\NdtResults\DTO\NdtResultData;
use App\Modules\NdtResults\Enums\NdtResultStatus;
use App\Modules\NdtResults\Models\NdtResult;
use App\Modules\NdtTasks\Enums\NdtTaskStatus;
use App\Modules\NdtTasks\Models\NdtTask;
use App\Modules\Notifications\Services\NotificationService;
use App\Modules\Welds\Enums\WeldStatus;
use App\Modules\Welds\Models\Weld;
use App\Modules\Welds\Services\WeldService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class NdtResultService
{
    use RecordsAuditLogs;

    public function __construct(
        private readonly EquipmentAvailabilityServiceInterface $equipmentAvailability,
        private readonly QualificationGuardService $qualificationGuard,
        private readonly WeldService $welds,
    ) {}

    /**
     * Create a result from a task and weld pairing.
     */
    public function create(NdtResultData $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): NdtResult
    {
        return DB::transaction(function () use ($data, $actor, $ipAddress, $userAgent): NdtResult {
            $task = NdtTask::query()->with(['assigneeEmployee', 'method'])->findOrFail($data->ndtTaskId);
            $weld = Weld::query()->findOrFail($data->weldId);

            $this->assertTaskAndWeldMatch($task, $weld, $data->ndtMethodId);
            $this->equipmentAvailability->ensureAvailable($data->equipmentId, $actor);

            $executorEmployeeId = $data->executorEmployeeId ?? $task->assignee_employee_id;

            if ($executorEmployeeId === null) {
                throw ValidationException::withMessages([
                    'executor_employee_id' => 'Для результата нужно указать исполнителя.',
                ]);
            }

            $executorEmployee = Employee::query()->with(['qualifications'])->findOrFail($executorEmployeeId);
            $this->qualificationGuard->ensureQualified($executorEmployee, $task->method->code, $actor);

            $result = NdtResult::query()->create([
                'ndt_task_id' => $task->getKey(),
                'weld_id' => $weld->getKey(),
                'ndt_method_id' => $data->ndtMethodId,
                'executor_employee_id' => $executorEmployeeId,
                'equipment_id' => $data->equipmentId,
                'normative_document_id' => $data->normativeDocumentId,
                'control_date' => $data->controlDate,
                'result_text' => $data->resultText,
                'comment' => $data->comment,
                'status' => NdtResultStatus::Created->value,
            ]);

            $this->recordStatusHistory($result, null, NdtResultStatus::Created, $actor, $data->comment);
            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: NdtResult::class,
                    entityId: $result->getKey(),
                    operation: 'ndt_result.created',
                    after: $this->snapshot($result->refresh()),
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $result->refresh();
        });
    }

    /**
     * Update the common result fields while it is still editable.
     */
    public function update(NdtResult $result, NdtResultData $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): NdtResult
    {
        if (! in_array($result->status, [NdtResultStatus::Created, NdtResultStatus::Returned], true)) {
            throw ValidationException::withMessages([
                'status' => 'Редактирование результата доступно только в черновике или после возврата.',
            ]);
        }

        return DB::transaction(function () use ($result, $data, $actor, $ipAddress, $userAgent): NdtResult {
            $before = $this->snapshot($result);
            $this->equipmentAvailability->ensureAvailable($data->equipmentId, $actor);

            $result->loadMissing('method');
            $executorEmployee = Employee::query()->with(['qualifications'])->findOrFail($data->executorEmployeeId ?? $result->executor_employee_id);
            $this->qualificationGuard->ensureQualified($executorEmployee, $result->method->code, $actor);

            $result->fill([
                'executor_employee_id' => $data->executorEmployeeId,
                'equipment_id' => $data->equipmentId,
                'normative_document_id' => $data->normativeDocumentId,
                'control_date' => $data->controlDate,
                'result_text' => $data->resultText,
                'comment' => $data->comment,
            ])->save();

            $result->refresh();

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: NdtResult::class,
                    entityId: $result->getKey(),
                    operation: 'ndt_result.updated',
                    before: $before,
                    after: $this->snapshot($result),
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $result;
        });
    }

    public function sendToAnalysis(NdtResult $result, ?User $actor = null, ?string $comment = null, ?string $ipAddress = null, ?string $userAgent = null): NdtResult
    {
        return $this->transition($result, NdtResultStatus::InAnalysis, [NdtResultStatus::Created, NdtResultStatus::Returned], 'ndt_result.sent_to_analysis', $actor, $comment, $ipAddress, $userAgent, WeldStatus::WaitingAnalysis);
    }

    public function markDefect(NdtResult $result, ?User $actor = null, ?string $comment = null, ?string $ipAddress = null, ?string $userAgent = null): NdtResult
    {
        return $this->transition($result, NdtResultStatus::Defect, [NdtResultStatus::Created, NdtResultStatus::InAnalysis, NdtResultStatus::Returned], 'ndt_result.defect_marked', $actor, $comment, $ipAddress, $userAgent, WeldStatus::Defect);
    }

    public function markReadyForConclusion(NdtResult $result, ?User $actor = null, ?string $comment = null, ?string $ipAddress = null, ?string $userAgent = null): NdtResult
    {
        if ($result->defects()->exists()) {
            throw ValidationException::withMessages([
                'status' => 'Нельзя отметить результат готовым к заключению при наличии дефектов.',
            ]);
        }

        return $this->transition($result, NdtResultStatus::ReadyForConclusion, [NdtResultStatus::InAnalysis], 'ndt_result.ready_for_conclusion', $actor, $comment, $ipAddress, $userAgent, WeldStatus::Good);
    }

    public function returnForCorrection(NdtResult $result, ?User $actor = null, ?string $comment = null, ?string $ipAddress = null, ?string $userAgent = null): NdtResult
    {
        return $this->transition($result, NdtResultStatus::Returned, [NdtResultStatus::InAnalysis, NdtResultStatus::ReadyForConclusion, NdtResultStatus::Defect], 'ndt_result.returned', $actor, $comment, $ipAddress, $userAgent, WeldStatus::WaitingAnalysis);
    }

    public function approve(NdtResult $result, ?User $actor = null, ?string $comment = null, ?string $ipAddress = null, ?string $userAgent = null): NdtResult
    {
        return $this->transition($result, NdtResultStatus::Approved, [NdtResultStatus::ReadyForConclusion], 'ndt_result.approved', $actor, $comment, $ipAddress, $userAgent, WeldStatus::Good);
    }

    public function addDefect(NdtResult $result, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): void
    {
        $before = [
            'result_id' => $result->getKey(),
            'defect_count' => $result->defects()->count(),
        ];

        $result->defects()->create([
            'defect_type_id' => $data['defect_type_id'] ?? null,
            'description' => $data['description'],
            'comment' => $data['comment'] ?? null,
        ]);

        if ($result->status !== NdtResultStatus::Defect) {
            $this->markDefect($result->refresh(), $actor, 'Добавлен дефект', $ipAddress, $userAgent);
        }

        $this->recordAudit(
            AuditData::forModelChange(
                entityType: NdtResult::class,
                entityId: $result->getKey(),
                operation: 'ndt_result.defect_added',
                before: $before,
                after: [
                    'result_id' => $result->getKey(),
                    'defect_count' => $result->defects()->count(),
                ],
                actor: $actor,
                reason: $data['comment'] ?? null,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            ),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function snapshot(NdtResult $result): array
    {
        return [
            'id' => $result->getKey(),
            'ndt_task_id' => $result->ndt_task_id,
            'weld_id' => $result->weld_id,
            'ndt_method_id' => $result->ndt_method_id,
            'executor_employee_id' => $result->executor_employee_id,
            'equipment_id' => $result->equipment_id,
            'status' => $result->status->value,
        ];
    }

    private function transition(
        NdtResult $result,
        NdtResultStatus $toStatus,
        array $allowedFromStatuses,
        string $operation,
        ?User $actor = null,
        ?string $comment = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        ?WeldStatus $weldStatus = null,
    ): NdtResult {
        if ($result->status === $toStatus) {
            return $result;
        }

        if (! in_array($result->status, $allowedFromStatuses, true)) {
            throw ValidationException::withMessages([
                'status' => 'Нельзя выполнить это действие из текущего статуса результата.',
            ]);
        }

        return DB::transaction(function () use ($result, $toStatus, $operation, $actor, $comment, $ipAddress, $userAgent, $weldStatus): NdtResult {
            $before = $this->snapshot($result);
            $previousStatus = $result->status;
            $result->status = $toStatus;
            $result->analyzed_at = in_array($toStatus, [NdtResultStatus::Defect, NdtResultStatus::ReadyForConclusion, NdtResultStatus::Approved], true)
                ? now()
                : $result->analyzed_at;
            $result->save();
            $result->refresh();

            $this->recordStatusHistory($result, $previousStatus, $toStatus, $actor, $comment);
            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: NdtResult::class,
                    entityId: $result->getKey(),
                    operation: $operation,
                    before: $before,
                    after: $this->snapshot($result),
                    actor: $actor,
                    reason: $comment,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            if ($weldStatus !== null) {
                $this->welds->updateStatus(
                    weld: $result->weld,
                    status: $weldStatus,
                    comment: $comment,
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                );
            }

            $notifications = app(NotificationService::class);

            if ($toStatus === NdtResultStatus::InAnalysis) {
                $notifications->notifyResultWaitingAnalysis($result);
            } elseif ($toStatus === NdtResultStatus::Defect) {
                $notifications->notifyDefectFound($result);
            }

            return $result;
        });
    }

    private function assertTaskAndWeldMatch(NdtTask $task, Weld $weld, int $methodId): void
    {
        if ($task->object_id !== $weld->object_id) {
            throw ValidationException::withMessages([
                'weld_id' => 'Стык должен относиться к тому же объекту/участку, что и задание.',
            ]);
        }

        if ((int) $task->ndt_method_id !== $methodId) {
            throw ValidationException::withMessages([
                'ndt_method_id' => 'Метод результата должен совпадать с методом задания.',
            ]);
        }

        if ($task->status === NdtTaskStatus::Cancelled) {
            throw ValidationException::withMessages([
                'ndt_task_id' => 'Нельзя создать результат по отмененному заданию.',
            ]);
        }

        if ($task->welds()->whereKey($weld->getKey())->doesntExist()) {
            throw ValidationException::withMessages([
                'weld_id' => 'Стык должен входить в состав задания.',
            ]);
        }
    }

    private function recordStatusHistory(NdtResult $result, NdtResultStatus|string|null $fromStatus, NdtResultStatus $toStatus, ?User $actor = null, ?string $comment = null): void
    {
        $result->statusHistory()->create([
            'from_status' => $fromStatus instanceof NdtResultStatus ? $fromStatus->value : $fromStatus,
            'to_status' => $toStatus->value,
            'changed_by_user_id' => $actor?->getKey(),
            'comment' => $comment,
        ]);
    }
}
