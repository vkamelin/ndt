<?php

declare(strict_types=1);

namespace App\Modules\Welds\Policies;

use App\Models\User;
use App\Modules\Welds\Models\Weld;

final class WeldPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('welds.view_any');
    }

    public function view(User $user, Weld $weld): bool
    {
        if ($user->can('welds.manage')) {
            return true;
        }

        return $user->objectId() === $weld->object_id;
    }

    public function manage(User $user, Weld $weld): bool
    {
        if (! $user->can('welds.manage')) {
            return false;
        }

        return $user->hasRole('Администратор системы') || $user->objectId() === $weld->object_id;
    }
}
