<?php

declare(strict_types=1);

namespace App\Modules\NdtTasks\Policies;

use App\Models\User;
use App\Modules\NdtTasks\Models\NdtTaskItem;

final class NdtTaskItemPolicy
{
    public function view(User $user, NdtTaskItem $item): bool
    {
        return $this->manage($user, $item) || $user->can('ndt_tasks.view_any');
    }

    public function manage(User $user, NdtTaskItem $item): bool
    {
        $task = $item->task()->with('assigneeEmployee')->first();

        if ($task === null) {
            return false;
        }

        if ($user->hasRole('Администратор системы')) {
            return true;
        }

        if ($user->can('ndt_tasks.manage') && $user->objectId() === $task->object_id) {
            return true;
        }

        if ($task->assignee_employee_id === null) {
            return false;
        }

        return $user->employees()->whereKey($task->assignee_employee_id)->exists();
    }
}
