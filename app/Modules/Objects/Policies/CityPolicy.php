<?php

declare(strict_types=1);

namespace App\Modules\Objects\Policies;

use App\Models\User;
use App\Modules\Objects\Models\City;

final class CityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('cities.view_any');
    }

    public function manage(User $user, City $city): bool
    {
        return $user->can('cities.manage');
    }
}
