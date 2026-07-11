<?php

declare(strict_types=1);

namespace App\Modules\Equipment\Services;

use App\Models\User;
use App\Modules\Equipment\Models\Equipment;
use App\Modules\NdtResults\Services\EquipmentAvailabilityServiceInterface;
use Illuminate\Validation\ValidationException;

final class EquipmentAvailabilityService implements EquipmentAvailabilityServiceInterface
{
    public function ensureAvailable(?int $equipmentId, ?User $actor = null): void
    {
        if ($equipmentId === null) {
            return;
        }

        $equipment = Equipment::query()
            ->with(['object', 'latestVerification', 'latestCalibration'])
            ->findOrFail($equipmentId);

        $this->ensureWithinScope($equipment, $actor);

        if (! $equipment->isUsable()) {
            throw ValidationException::withMessages([
                'equipment_id' => 'Выбранное оборудование недоступно для использования.',
            ]);
        }

        if (! config('equipment.strict_qualification_guard')) {
            return;
        }

        $today = today();
        $verification = $equipment->latestVerification;
        $calibration = $equipment->latestCalibration;

        if ($verification === null || ($verification->valid_until !== null && $verification->valid_until->lt($today))) {
            throw ValidationException::withMessages([
                'equipment_id' => 'Для выбранного оборудования отсутствует действующая поверка.',
            ]);
        }

        if ($calibration === null || ($calibration->valid_until !== null && $calibration->valid_until->lt($today))) {
            throw ValidationException::withMessages([
                'equipment_id' => 'Для выбранного оборудования отсутствует действующая калибровка.',
            ]);
        }
    }

    private function ensureWithinScope(Equipment $equipment, ?User $actor = null): void
    {
        if ($actor === null || $actor->hasRole('Администратор системы')) {
            return;
        }

        if ($actor->objectId() !== $equipment->object_id) {
            throw ValidationException::withMessages([
                'equipment_id' => 'Оборудование должно относиться к вашему объекту/участку.',
            ]);
        }
    }
}
