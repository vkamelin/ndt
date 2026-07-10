<?php

declare(strict_types=1);

namespace App\Modules\Registers\Policies;

use App\Models\User;
use App\Modules\Registers\Models\Act;

final class ActPolicy
{
    public function view(User $user, Act $act): bool
    {
        return $user->can('registers.view_any') && $this->scopeMatches($user, $act);
    }

    public function manage(User $user, Act $act): bool
    {
        return $user->can('registers.manage') && $this->scopeMatches($user, $act);
    }

    private function scopeMatches(User $user, Act $act): bool
    {
        if ($user->hasRole('Администратор системы')) {
            return true;
        }

        return $act->object_id !== null && $user->objectId() === $act->object_id;
    }
}
