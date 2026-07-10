<?php

declare(strict_types=1);

namespace App\Modules\NdtResults\Services;

use App\Models\User;

final class NullEquipmentAvailabilityService implements EquipmentAvailabilityServiceInterface
{
    public function ensureAvailable(?int $equipmentId, ?User $actor = null): void
    {
        // Full equipment checks are introduced in stage 8.
    }
}
