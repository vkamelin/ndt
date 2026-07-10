<?php

declare(strict_types=1);

namespace App\Modules\NdtResults\Policies;

use App\Models\User;
use App\Modules\NdtResults\Enums\NdtResultStatus;
use App\Modules\NdtResults\Models\NdtResult;
use App\Modules\NdtTasks\Models\NdtTask;

final class NdtResultPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ndt_results.view_any');
    }

    public function view(User $user, NdtResult $result): bool
    {
        if ($this->canManageScope($user, $result)) {
            return true;
        }

        return $user->employees()->whereKey($result->executor_employee_id)->exists();
    }

    public function create(User $user, NdtTask $task): bool
    {
        if (! $user->can('ndt_results.manage')) {
            return false;
        }

        if ($user->hasRole('Администратор системы')) {
            return true;
        }

        if ($user->objectId() !== $task->object_id) {
            return false;
        }

        return $user->hasRole('Дефектоскопист')
            && $user->employees()->whereKey($task->assignee_employee_id)->exists();
    }

    public function update(User $user, NdtResult $result): bool
    {
        return $this->manage($user, $result) && in_array($result->status, [NdtResultStatus::Created, NdtResultStatus::Returned], true);
    }

    public function manage(User $user, NdtResult $result): bool
    {
        return $this->canManageScope($user, $result);
    }

    public function analyze(User $user, NdtResult $result): bool
    {
        return $this->canAnalyzeScope($user, $result);
    }

    public function approve(User $user, NdtResult $result): bool
    {
        return $this->canApproveScope($user, $result) && in_array($result->status, [NdtResultStatus::ReadyForConclusion], true);
    }

    private function canManageScope(User $user, NdtResult $result): bool
    {
        if (! $user->can('ndt_results.manage')) {
            return false;
        }

        return $user->hasRole('Администратор системы') || $user->objectId() === $result->weld->object_id;
    }

    private function canAnalyzeScope(User $user, NdtResult $result): bool
    {
        if (! $user->can('ndt_results.analyze')) {
            return false;
        }

        return $user->hasRole('Администратор системы') || $user->objectId() === $result->weld->object_id;
    }

    private function canApproveScope(User $user, NdtResult $result): bool
    {
        if (! $user->can('ndt_results.approve')) {
            return false;
        }

        return $user->hasRole('Администратор системы') || $user->objectId() === $result->weld->object_id;
    }
}
