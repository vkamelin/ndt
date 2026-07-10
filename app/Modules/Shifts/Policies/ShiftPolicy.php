<?php

declare(strict_types=1);

namespace App\Modules\Shifts\Policies;

use App\Models\User;
use App\Modules\Employees\Models\Employee;
use App\Modules\Shifts\Models\Shift;

final class ShiftPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('shifts.view_any');
    }

    public function create(User $user, Employee $employee): bool
    {
        return $user->can('shifts.manage') && $this->scopeMatchesByEmployee($user, $employee);
    }

    public function view(User $user, Shift $shift): bool
    {
        return $this->manage($user, $shift) || $this->scopeMatches($user, $shift);
    }

    public function manage(User $user, Shift $shift): bool
    {
        return $user->can('shifts.manage') && $this->scopeMatches($user, $shift);
    }

    private function scopeMatches(User $user, Shift $shift): bool
    {
        if ($user->hasRole('Администратор системы')) {
            return true;
        }

        return $user->objectId() === $shift->object_id;
    }

    private function scopeMatchesByEmployee(User $user, Employee $employee): bool
    {
        if ($user->hasRole('Администратор системы')) {
            return true;
        }

        return $user->objectId() === $employee->object_id;
    }
}
