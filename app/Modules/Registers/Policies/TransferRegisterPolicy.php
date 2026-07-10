<?php

declare(strict_types=1);

namespace App\Modules\Registers\Policies;

use App\Models\User;
use App\Modules\Registers\Models\TransferRegister;

final class TransferRegisterPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('registers.view_any');
    }

    public function view(User $user, TransferRegister $register): bool
    {
        return $user->can('registers.view_any') && $this->scopeMatches($user, $register);
    }

    public function manage(User $user, TransferRegister $register): bool
    {
        return $user->can('registers.manage') && $this->scopeMatches($user, $register);
    }

    public function transition(User $user, TransferRegister $register): bool
    {
        return $user->can('registers.transfer') && $this->scopeMatches($user, $register);
    }

    public function archive(User $user, TransferRegister $register): bool
    {
        return $user->can('registers.archive') && $this->scopeMatches($user, $register);
    }

    public function act(User $user, TransferRegister $register): bool
    {
        return $user->can('registers.act') && $this->scopeMatches($user, $register);
    }

    private function scopeMatches(User $user, TransferRegister $register): bool
    {
        if ($user->hasRole('Администратор системы')) {
            return true;
        }

        return $register->object_id !== null && $user->objectId() === $register->object_id;
    }
}
