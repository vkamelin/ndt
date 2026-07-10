<?php

declare(strict_types=1);

namespace App\Modules\Notifications\Services;

use App\Models\User;
use App\Modules\Conclusions\Models\Conclusion;
use App\Modules\Employees\Models\Employee;
use App\Modules\Employees\Models\EmployeeQualification;
use App\Modules\Equipment\Models\EquipmentCalibration;
use App\Modules\Equipment\Models\EquipmentVerification;
use App\Modules\Inventory\Models\ChemicalRequest;
use App\Modules\NdtRequests\Models\NdtRequest;
use App\Modules\NdtResults\Models\NdtResult;
use App\Modules\NdtTasks\Models\NdtTask;
use App\Modules\Notifications\Enums\NotificationChannel;
use App\Modules\Notifications\Enums\NotificationDeliveryStatus;
use App\Modules\Notifications\Enums\NotificationType;
use App\Modules\Notifications\Jobs\SendNotificationEmailJob;
use App\Modules\Notifications\Models\Notification;
use App\Modules\Notifications\Models\NotificationDelivery;
use App\Modules\Notifications\Models\NotificationTemplate;
use App\Modules\Radiography\Models\RtResult;
use App\Modules\Reports\Models\ReportJob;
use App\Modules\Shifts\Models\Shift;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class NotificationService
{
    /**
     * @param  array<string, scalar|null>  $context
     */
    public function notifyUser(User $user, NotificationType $type, string $templateCode, array $context = [], array $meta = []): ?Notification
    {
        return $this->notifyUsers(collect([$user]), $type, $templateCode, $context, $meta)->first();
    }

    /**
     * @param  iterable<User>  $users
     * @param  array<string, scalar|null>  $context
     * @return Collection<int, Notification>
     */
    public function notifyUsers(iterable $users, NotificationType $type, string $templateCode, array $context = [], array $meta = []): Collection
    {
        $template = $this->template($templateCode);

        if ($template === null) {
            return collect();
        }

        $rendered = $template->render($context);
        $fingerprint = $this->fingerprint($template->code, $context);
        $notifications = collect();

        foreach ($this->uniqueUsers($users) as $user) {
            $notification = DB::transaction(function () use ($user, $template, $type, $rendered, $context, $meta, $fingerprint): Notification {
                $existing = Notification::query()
                    ->where('user_id', $user->getKey())
                    ->where('type', $type->value)
                    ->whereDate('created_at', today())
                    ->where('data->fingerprint', $fingerprint)
                    ->first();

                if ($existing !== null) {
                    return $existing;
                }

                $notification = Notification::query()->create([
                    'user_id' => $user->getKey(),
                    'notification_template_id' => $template->getKey(),
                    'type' => $type->value,
                    'title' => $rendered['title'],
                    'body' => $rendered['body'],
                    'data' => $this->notificationData($context, $meta, $fingerprint, $type),
                    'read_at' => null,
                ]);

                NotificationDelivery::query()->create([
                    'notification_id' => $notification->getKey(),
                    'channel' => NotificationChannel::Database->value,
                    'recipient_address' => (string) $user->getKey(),
                    'status' => NotificationDeliveryStatus::Sent->value,
                    'queued_at' => now(),
                    'sent_at' => now(),
                    'failed_at' => null,
                    'error_message' => null,
                    'meta' => ['channel' => 'database'],
                ]);

                if ($this->templateUsesEmail($template) && $user->email !== '') {
                    $delivery = NotificationDelivery::query()->create([
                        'notification_id' => $notification->getKey(),
                        'channel' => NotificationChannel::Email->value,
                        'recipient_address' => $user->email,
                        'status' => NotificationDeliveryStatus::Queued->value,
                        'queued_at' => now(),
                        'sent_at' => null,
                        'failed_at' => null,
                        'error_message' => null,
                        'meta' => ['channel' => 'email'],
                    ]);

                    SendNotificationEmailJob::dispatch($delivery->getKey())->afterCommit();
                }

                return $notification;
            });

            $notifications->push($notification);
        }

        return $notifications;
    }

    public function markRead(Notification $notification, User $actor): Notification
    {
        if ((int) $notification->user_id !== (int) $actor->getKey() && ! $actor->can('notifications.view_any')) {
            abort(403, 'Доступ запрещен.');
        }

        $notification->markAsRead();

        return $notification->refresh();
    }

    public function markAllRead(User $actor): int
    {
        return Notification::query()
            ->where('user_id', $actor->getKey())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function notifyTaskAssigned(NdtTask $task): void
    {
        $task->loadMissing(['method', 'request.object', 'object', 'assigneeEmployee.users']);

        $users = $task->assigneeEmployee?->users ?? collect();

        $this->notifyUsers($users, NotificationType::TaskAssigned, 'task_assigned', [
            'task_number' => $task->task_number,
            'method_label' => $task->method?->label() ?? '',
            'request_number' => $task->request?->request_number ?? '',
            'object_name' => $task->object?->name ?? '',
            'planned_date' => $task->planned_date?->format('d.m.Y'),
        ], [
            'ndt_task_id' => $task->getKey(),
        ]);
    }

    public function notifyTaskOverdue(NdtTask $task): void
    {
        $task->loadMissing(['method', 'object', 'assigneeEmployee.users']);

        $this->notifyUsers($task->assigneeEmployee?->users ?? collect(), NotificationType::TaskOverdue, 'task_overdue', [
            'task_number' => $task->task_number,
            'method_label' => $task->method?->label() ?? '',
            'planned_date' => $task->planned_date?->format('d.m.Y'),
            'object_name' => $task->object?->name ?? '',
        ], [
            'ndt_task_id' => $task->getKey(),
        ]);
    }

    public function notifyRequestClarification(NdtRequest $request): void
    {
        $request->loadMissing(['object']);

        $this->notifyObjectRoles($request->object_id, NotificationType::RequestClarification, 'request_clarification', [
            'request_number' => $request->request_number,
            'object_name' => $request->object?->name ?? '',
        ], [
            'ndt_request_id' => $request->getKey(),
        ], ['Начальник участка']);
    }

    public function notifyResultWaitingAnalysis(NdtResult $result): void
    {
        $result->loadMissing(['weld.object', 'method']);

        $this->notifyObjectRoles($result->weld->object_id, NotificationType::ResultWaitingAnalysis, 'result_waiting_analysis', [
            'result_id' => $result->getKey(),
            'weld_number' => $result->weld->weld_number,
            'method_label' => $result->method?->label() ?? '',
        ], [
            'ndt_result_id' => $result->getKey(),
        ], ['Инженер НК / Дешифровщик']);
    }

    public function notifyDefectFound(NdtResult $result): void
    {
        $result->loadMissing(['weld.object', 'method']);

        $this->notifyObjectRoles($result->weld->object_id, NotificationType::DefectFound, 'defect_found', [
            'result_id' => $result->getKey(),
            'weld_number' => $result->weld->weld_number,
            'method_label' => $result->method?->label() ?? '',
        ], [
            'ndt_result_id' => $result->getKey(),
        ], ['Инженер НК / Дешифровщик']);
    }

    public function notifyConclusionWaitingApproval(Conclusion $conclusion): void
    {
        $conclusion->loadMissing(['object']);

        $this->notifyObjectRoles($conclusion->object_id, NotificationType::ConclusionWaitingApproval, 'conclusion_waiting_approval', [
            'conclusion_number' => $conclusion->number,
            'object_name' => $conclusion->object?->name ?? '',
        ], [
            'conclusion_id' => $conclusion->getKey(),
        ], ['Начальник участка']);
    }

    public function notifyConclusionReturned(Conclusion $conclusion): void
    {
        $conclusion->loadMissing(['object']);

        $this->notifyObjectRoles($conclusion->object_id, NotificationType::ConclusionReturned, 'conclusion_returned', [
            'conclusion_number' => $conclusion->number,
            'object_name' => $conclusion->object?->name ?? '',
        ], [
            'conclusion_id' => $conclusion->getKey(),
        ], ['Начальник участка']);
    }

    public function notifyReshootRequired(RtResult $result): void
    {
        $result->loadMissing(['ndtResult.weld.object']);

        $this->notifyObjectRoles($result->ndtResult?->weld->object_id ?? 0, NotificationType::ReshootRequired, 'reshoot_required', [
            'rt_result_id' => $result->getKey(),
        ], [
            'rt_result_id' => $result->getKey(),
        ], ['Инженер НК / Дешифровщик']);
    }

    public function notifyEquipmentVerificationExpiring(EquipmentVerification $verification): void
    {
        $verification->loadMissing(['equipment.object']);

        $this->notifyObjectRoles($verification->equipment->object_id, NotificationType::EquipmentVerificationExpiring, 'equipment_verification_expiring', [
            'equipment_name' => $verification->equipment->name,
            'valid_until' => $verification->valid_until?->format('d.m.Y'),
        ], [
            'equipment_verification_id' => $verification->getKey(),
        ], ['Начальник участка']);
    }

    public function notifyEquipmentCalibrationExpiring(EquipmentCalibration $calibration): void
    {
        $calibration->loadMissing(['equipment.object']);

        $this->notifyObjectRoles($calibration->equipment->object_id, NotificationType::EquipmentCalibrationExpiring, 'equipment_calibration_expiring', [
            'equipment_name' => $calibration->equipment->name,
            'valid_until' => $calibration->valid_until?->format('d.m.Y'),
        ], [
            'equipment_calibration_id' => $calibration->getKey(),
        ], ['Начальник участка']);
    }

    public function notifyQualificationExpiring(EmployeeQualification $qualification): void
    {
        $qualification->loadMissing(['employee.object', 'employee.users']);

        $users = $qualification->employee?->users ?? collect();

        $this->notifyUsers($users, NotificationType::QualificationExpiring, 'qualification_expiring', [
            'employee_name' => $qualification->employee?->fullName() ?? '',
            'method_label' => $qualification->method?->label() ?? '',
            'valid_until' => $qualification->valid_until?->format('d.m.Y'),
        ], [
            'employee_qualification_id' => $qualification->getKey(),
        ]);

        $this->notifyObjectRoles($qualification->employee?->object_id ?? 0, NotificationType::QualificationExpiring, 'qualification_expiring', [
            'employee_name' => $qualification->employee?->fullName() ?? '',
            'method_label' => $qualification->method?->label() ?? '',
            'valid_until' => $qualification->valid_until?->format('d.m.Y'),
        ], [
            'employee_qualification_id' => $qualification->getKey(),
        ], ['Начальник участка']);
    }

    public function notifyShiftIncomplete(Shift $shift): void
    {
        $shift->loadMissing(['employee.users', 'object']);

        $this->notifyUsers($shift->employee?->users ?? collect(), NotificationType::ShiftIncomplete, 'shift_incomplete', [
            'shift_id' => $shift->getKey(),
            'object_name' => $shift->object?->name ?? '',
        ], [
            'shift_id' => $shift->getKey(),
        ]);
    }

    public function notifyReportReady(ReportJob $reportJob): void
    {
        $reportJob->loadMissing(['requestedBy', 'object.city']);

        if ($reportJob->requestedBy === null) {
            return;
        }

        $this->notifyUser($reportJob->requestedBy, NotificationType::ReportReady, 'report_ready', [
            'report_title' => $reportJob->title,
            'report_type' => $reportJob->report_type instanceof \App\Modules\Reports\Enums\ReportType ? $reportJob->report_type->label() : (string) $reportJob->report_type,
            'object_name' => $reportJob->object?->name ?? '',
        ], [
            'report_job_id' => $reportJob->getKey(),
            'file_id' => $reportJob->file?->getKey(),
        ]);
    }

    public function notifyChemicalRequired(ChemicalRequest $request): void
    {
        $request->loadMissing(['shift.employee.users', 'shift.object', 'chemicalType']);

        $this->notifyUsers($request->shift?->employee?->users ?? collect(), NotificationType::ChemicalRequired, 'chemical_required', [
            'shift_id' => $request->shift_id,
            'chemical_name' => $request->chemicalType?->name ?? '',
        ], [
            'chemical_request_id' => $request->getKey(),
        ]);
    }

    public function notifyQueueFailure(string $message, array $context = []): void
    {
        $users = User::query()
            ->role('Администратор системы')
            ->get();

        $this->notifyUsers($users, NotificationType::QueueFailure, 'queue_failure', array_merge([
            'message' => $message,
        ], $context), [
            'source' => 'queue',
        ]);
    }

    private function template(string $templateCode): ?NotificationTemplate
    {
        return NotificationTemplate::query()
            ->where('code', $templateCode)
            ->where('is_active', true)
            ->first();
    }

    private function templateUsesEmail(NotificationTemplate $template): bool
    {
        return in_array('email', $template->channels ?? [], true);
    }

    /**
     * @param  iterable<User>  $users
     * @return array<int, User>
     */
    private function uniqueUsers(iterable $users): array
    {
        $unique = [];

        foreach ($users as $user) {
            $unique[$user->getKey()] = $user;
        }

        return array_values($unique);
    }

    /**
     * @param  array<string, scalar|null>  $context
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    private function notificationData(array $context, array $meta, string $fingerprint, NotificationType $type): array
    {
        return array_merge($meta, [
            'type' => $type->value,
            'fingerprint' => $fingerprint,
            'context' => $context,
        ]);
    }

    /**
     * @param  array<string, scalar|null>  $context
     */
    private function fingerprint(string $code, array $context): string
    {
        $normalized = $this->normalizeContext($context);

        return hash('sha256', $code.'|'.json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    /**
     * @param  array<string, scalar|null>  $context
     * @return array<string, scalar|null>
     */
    private function normalizeContext(array $context): array
    {
        ksort($context);

        return array_map(static function (mixed $value): mixed {
            if (is_array($value)) {
                ksort($value);

                return $value;
            }

            return $value;
        }, $context);
    }

    /**
     * @param  array<int, string>  $roleNames
     * @param  array<string, scalar|null>  $context
     * @param  array<string, mixed>  $meta
     */
    private function notifyObjectRoles(int $objectId, NotificationType $type, string $templateCode, array $context = [], array $meta = [], array $roleNames = []): void
    {
        if ($objectId <= 0) {
            return;
        }

        $users = User::query()
            ->whereHas('employees', function ($query) use ($objectId): void {
                $query->where('object_id', $objectId);
            })
            ->whereHas('roles', function ($query) use ($roleNames): void {
                $query->whereIn('name', $roleNames);
            })
            ->get();

        if ($users->isNotEmpty()) {
            $this->notifyUsers($users, $type, $templateCode, $context, $meta);
        }

        $adminUsers = User::query()
            ->role('Администратор системы')
            ->get();

        if ($adminUsers->isNotEmpty()) {
            $this->notifyUsers($adminUsers, $type, $templateCode, $context, $meta);
        }
    }
}
