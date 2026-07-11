<?php

declare(strict_types=1);

namespace App\Modules\Access\Policies;

use App\Models\User;

final class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('users.view');
    }

    public function view(User $user, User $target): bool
    {
        return $user->is($target) || $user->can('profile.view');
    }

    public function assignRoles(User $user, User $target): bool
    {
        return $user->can('users.manage') && ! $user->is($target);
    }

    public function update(User $user, User $target): bool
    {
        return $user->can('users.manage') && ! $user->is($target);
    }

    public function block(User $user, User $target): bool
    {
        return $user->can('users.manage') && ! $user->is($target);
    }

    public function unblock(User $user, User $target): bool
    {
        return $user->can('users.manage') && ! $user->is($target);
    }
}
