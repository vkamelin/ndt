<?php

declare(strict_types=1);

namespace App\Modules\Access\Services;

use App\Models\User;

final class AccessService
{
    public function isAdministrator(User $user): bool
    {
        return $user->can('users.manage');
    }
}
