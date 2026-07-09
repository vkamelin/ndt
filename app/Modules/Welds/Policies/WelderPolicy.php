<?php

declare(strict_types=1);

namespace App\Modules\Welds\Policies;

use App\Models\User;
use App\Modules\Welds\Models\Welder;

final class WelderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('welders.view_any');
    }

    public function view(User $user, Welder $welder): bool
    {
        return $user->can('welders.view_any');
    }

    public function manage(User $user, Welder $welder): bool
    {
        return $user->can('welders.manage');
    }
}
