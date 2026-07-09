<?php

declare(strict_types=1);

namespace App\Modules\Organizations\Policies;

use App\Models\User;
use App\Modules\Organizations\Models\Organization;

final class OrganizationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('organizations.view_any');
    }

    public function view(User $user, Organization $organization): bool
    {
        return $user->can('organizations.view_any');
    }

    public function manage(User $user, Organization $organization): bool
    {
        return $user->can('organizations.manage');
    }
}
