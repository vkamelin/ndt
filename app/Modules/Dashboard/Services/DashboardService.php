<?php

declare(strict_types=1);

namespace App\Modules\Dashboard\Services;

use App\Models\User;
use App\Modules\Employees\Models\EmployeeQualification;
use App\Modules\Equipment\Models\EquipmentCalibration;
use App\Modules\Equipment\Models\EquipmentVerification;
use App\Modules\NdtRequests\Enums\NdtRequestStatus;
use App\Modules\NdtRequests\Models\NdtRequest;
use App\Modules\NdtTasks\Enums\NdtTaskStatus;
use App\Modules\NdtTasks\Models\NdtTask;
use App\Modules\Notifications\Models\Notification;
use App\Modules\Shifts\Enums\ShiftStatus;
use App\Modules\Shifts\Models\Shift;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class DashboardService
{
    /**
     * @return array{
     *     user: User,
     *     role_names: list<string>,
     *     unread_notifications_count: int,
     *     active_requests: Collection<int, NdtRequest>,
     *     overdue_tasks: Collection<int, NdtTask>,
     *     my_tasks: Collection<int, NdtTask>,
     *     approval_requests: Collection<int, NdtRequest>,
     *     open_shifts: Collection<int, Shift>,
     *     expiring_verifications: Collection<int, EquipmentVerification>,
     *     expiring_calibrations: Collection<int, EquipmentCalibration>,
     *     expiring_qualifications: Collection<int, EmployeeQualification>,
     *     latest_notifications: Collection<int, Notification>,
     *     strict_qualification_guard: bool,
     * }
     */
    public function overview(User $user): array
    {
        $objectId = $user->objectId();
        $isAdministrator = $user->hasRole('Администратор системы');
        $warningDays = config('equipment.warning_days');
        $myEmployeeId = $user->primaryEmployee()?->getKey();

        return [
            'user' => $user,
            'role_names' => $user->getRoleNames()->values()->all(),
            'unread_notifications_count' => Notification::query()
                ->where('user_id', $user->getKey())
                ->whereNull('read_at')
                ->count(),
            'active_requests' => $this->requestQuery($objectId, $isAdministrator)
                ->whereNotIn('status', [NdtRequestStatus::Closed->value, NdtRequestStatus::Cancelled->value, NdtRequestStatus::Draft->value])
                ->orderByDesc('created_at')
                ->limit(5)
                ->get(),
            'overdue_tasks' => $this->taskQuery($objectId, $isAdministrator)
                ->whereDate('planned_date', '<', today())
                ->whereNotIn('status', [NdtTaskStatus::Completed->value, NdtTaskStatus::Cancelled->value])
                ->orderBy('planned_date')
                ->limit(5)
                ->get(),
            'my_tasks' => $myEmployeeId === null
                ? NdtTask::query()->whereRaw('1 = 0')->get()
                : NdtTask::query()
                    ->with(['request.object.city', 'object.city', 'method', 'assigneeEmployee'])
                    ->where('assignee_employee_id', $myEmployeeId)
                    ->whereNotIn('status', [NdtTaskStatus::Completed->value, NdtTaskStatus::Cancelled->value])
                    ->orderByDesc('created_at')
                    ->limit(5)
                    ->get(),
            'approval_requests' => $this->requestQuery($objectId, $isAdministrator)
                ->where('status', NdtRequestStatus::Approval->value)
                ->orderByDesc('updated_at')
                ->limit(5)
                ->get(),
            'open_shifts' => $this->shiftQuery($objectId, $isAdministrator)
                ->whereIn('status', [ShiftStatus::Open->value, ShiftStatus::InProgress->value, ShiftStatus::AwaitingCompletion->value])
                ->orderByDesc('started_at')
                ->limit(5)
                ->get(),
            'expiring_verifications' => EquipmentVerification::query()
                ->with(['equipment.object.city'])
                ->whereNotNull('valid_until')
                ->whereDate('valid_until', '<=', today()->addDays((int) $warningDays['verification']))
                ->when(! $isAdministrator && $objectId !== null, function (Builder $query) use ($objectId): void {
                    $query->whereHas('equipment', function (Builder $equipmentQuery) use ($objectId): void {
                        $equipmentQuery->where('object_id', $objectId);
                    });
                })
                ->when(! $isAdministrator && $objectId === null, function (Builder $query): void {
                    $query->whereRaw('1 = 0');
                })
                ->orderBy('valid_until')
                ->limit(5)
                ->get(),
            'expiring_calibrations' => EquipmentCalibration::query()
                ->with(['equipment.object.city'])
                ->whereNotNull('valid_until')
                ->whereDate('valid_until', '<=', today()->addDays((int) $warningDays['calibration']))
                ->when(! $isAdministrator && $objectId !== null, function (Builder $query) use ($objectId): void {
                    $query->whereHas('equipment', function (Builder $equipmentQuery) use ($objectId): void {
                        $equipmentQuery->where('object_id', $objectId);
                    });
                })
                ->when(! $isAdministrator && $objectId === null, function (Builder $query): void {
                    $query->whereRaw('1 = 0');
                })
                ->orderBy('valid_until')
                ->limit(5)
                ->get(),
            'expiring_qualifications' => EmployeeQualification::query()
                ->with(['employee.object.city', 'employee.position'])
                ->whereNotNull('valid_until')
                ->whereDate('valid_until', '<=', today()->addDays((int) $warningDays['qualification']))
                ->when(! $isAdministrator && $objectId !== null, function (Builder $query) use ($objectId): void {
                    $query->whereHas('employee', function (Builder $employeeQuery) use ($objectId): void {
                        $employeeQuery->where('object_id', $objectId);
                    });
                })
                ->when(! $isAdministrator && $objectId === null, function (Builder $query): void {
                    $query->whereRaw('1 = 0');
                })
                ->orderBy('valid_until')
                ->limit(5)
                ->get(),
            'latest_notifications' => Notification::query()
                ->where('user_id', $user->getKey())
                ->latest()
                ->limit(8)
                ->get(),
            'strict_qualification_guard' => (bool) config('equipment.strict_qualification_guard'),
        ];
    }

    private function requestQuery(?int $objectId, bool $isAdministrator): Builder
    {
        return NdtRequest::query()
            ->with(['object.city', 'organization'])
            ->when(! $isAdministrator && $objectId !== null, function (Builder $query) use ($objectId): void {
                $query->where('object_id', $objectId);
            })
            ->when(! $isAdministrator && $objectId === null, function (Builder $query): void {
                $query->whereRaw('1 = 0');
            });
    }

    private function taskQuery(?int $objectId, bool $isAdministrator): Builder
    {
        return NdtTask::query()
            ->with(['request.object.city', 'object.city', 'method', 'assigneeEmployee'])
            ->when(! $isAdministrator && $objectId !== null, function (Builder $query) use ($objectId): void {
                $query->where('object_id', $objectId);
            })
            ->when(! $isAdministrator && $objectId === null, function (Builder $query): void {
                $query->whereRaw('1 = 0');
            });
    }

    private function shiftQuery(?int $objectId, bool $isAdministrator): Builder
    {
        return Shift::query()
            ->with(['employee.object.city', 'object.city', 'employee.position'])
            ->when(! $isAdministrator && $objectId !== null, function (Builder $query) use ($objectId): void {
                $query->where('object_id', $objectId);
            })
            ->when(! $isAdministrator && $objectId === null, function (Builder $query): void {
                $query->whereRaw('1 = 0');
            });
    }
}
