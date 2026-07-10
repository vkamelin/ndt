<?php

declare(strict_types=1);

namespace App\Modules\NdtResults\Services;

use App\Models\User;

interface EquipmentAvailabilityServiceInterface
{
    /**
     * Verify that the chosen equipment may be used for a result.
     */
    public function ensureAvailable(?int $equipmentId, ?User $actor = null): void;
}
