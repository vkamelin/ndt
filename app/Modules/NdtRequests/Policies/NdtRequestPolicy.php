<?php

declare(strict_types=1);

namespace App\Modules\NdtRequests\Policies;

use App\Models\User;
use App\Modules\NdtRequests\Models\NdtRequest;

final class NdtRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ndt_requests.view_any');
    }

    public function view(User $user, NdtRequest $request): bool
    {
        if ($user->can('ndt_requests.manage')) {
            return $user->hasRole('Администратор системы') || $user->objectId() === $request->object_id;
        }

        return $user->objectId() === $request->object_id;
    }

    public function manage(User $user, NdtRequest $request): bool
    {
        if (! $user->can('ndt_requests.manage')) {
            return false;
        }

        return $user->hasRole('Администратор системы') || $user->objectId() === $request->object_id;
    }
}
