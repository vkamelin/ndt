<?php

declare(strict_types=1);

namespace App\Modules\Access\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

final class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('roles.view');
    }

    public function manage(User $user, Role $role): bool
    {
        return $user->can('roles.manage');
    }
}
