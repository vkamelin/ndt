<?php

declare(strict_types=1);

namespace App\Modules\Objects\Policies;

use App\Models\User;
use App\Modules\Objects\Models\NdtObject;

final class ObjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('objects.view_any');
    }

    public function view(User $user, NdtObject $object): bool
    {
        return $user->can('objects.view_any') || $user->can('objects.manage');
    }

    public function manage(User $user, NdtObject $object): bool
    {
        return $user->can('objects.manage');
    }
}
