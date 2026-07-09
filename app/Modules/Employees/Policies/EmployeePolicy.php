<?php

declare(strict_types=1);

namespace App\Modules\Employees\Policies;

use App\Models\User;
use App\Modules\Employees\Models\Employee;

final class EmployeePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('employees.view_any');
    }

    public function view(User $user, Employee $employee): bool
    {
        if ($user->can('employees.manage')) {
            return true;
        }

        return $user->objectId() === $employee->object_id;
    }

    public function manage(User $user, Employee $employee): bool
    {
        return $user->can('employees.manage');
    }
}
