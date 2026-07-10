<?php

declare(strict_types=1);

namespace App\Modules\NdtTasks\Policies;

use App\Models\User;
use App\Modules\NdtTasks\Enums\NdtTaskStatus;
use App\Modules\NdtTasks\Models\NdtTask;

final class NdtTaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ndt_tasks.view_any');
    }

    public function create(User $user): bool
    {
        return $user->can('ndt_tasks.manage');
    }

    public function view(User $user, NdtTask $task): bool
    {
        if ($this->canManageTask($user, $task)) {
            return true;
        }

        return $this->isAssignee($user, $task);
    }

    public function update(User $user, NdtTask $task): bool
    {
        return $this->canManageTask($user, $task) && in_array($task->status, [NdtTaskStatus::Created, NdtTaskStatus::Assigned], true);
    }

    public function accept(User $user, NdtTask $task): bool
    {
        return $this->canManageTask($user, $task) || (
            $this->isAssignee($user, $task) &&
            in_array($task->status, [NdtTaskStatus::Assigned, NdtTaskStatus::Returned], true)
        );
    }

    public function startWork(User $user, NdtTask $task): bool
    {
        return $this->canManageTask($user, $task) || (
            $this->isAssignee($user, $task) &&
            $task->status === NdtTaskStatus::Accepted
        );
    }

    public function complete(User $user, NdtTask $task): bool
    {
        return $this->canManageTask($user, $task) || (
            $this->isAssignee($user, $task) &&
            $task->status === NdtTaskStatus::InWork
        );
    }

    public function completePartial(User $user, NdtTask $task): bool
    {
        return $this->complete($user, $task);
    }

    public function returnTask(User $user, NdtTask $task): bool
    {
        return $this->canManageTask($user, $task) && in_array($task->status, [NdtTaskStatus::Assigned, NdtTaskStatus::Accepted, NdtTaskStatus::InWork, NdtTaskStatus::Partial], true);
    }

    public function cancel(User $user, NdtTask $task): bool
    {
        return $this->canManageTask($user, $task) && ! in_array($task->status, [NdtTaskStatus::Completed, NdtTaskStatus::Cancelled], true);
    }

    private function canManageTask(User $user, NdtTask $task): bool
    {
        if (! $user->can('ndt_tasks.manage')) {
            return false;
        }

        return $user->hasRole('Администратор системы') || $user->objectId() === $task->object_id;
    }

    private function isAssignee(User $user, NdtTask $task): bool
    {
        if ($task->assignee_employee_id === null) {
            return false;
        }

        return $user->employees()->whereKey($task->assignee_employee_id)->exists();
    }
}
