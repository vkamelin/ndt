<?php

declare(strict_types=1);

namespace App\Modules\Equipment\Policies;

use App\Models\User;
use App\Modules\Equipment\Models\Equipment;

final class EquipmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('equipment.view_any');
    }

    public function view(User $user, Equipment $equipment): bool
    {
        return $user->can('equipment.view_any') || $user->can('equipment.manage');
    }

    public function manage(User $user, Equipment $equipment): bool
    {
        if (! $user->can('equipment.manage')) {
            return false;
        }

        return $user->hasRole('Администратор системы') || $user->objectId() === $equipment->object_id;
    }
}
