<?php

declare(strict_types=1);

namespace App\Modules\Api\Http\Resources;

use App\Modules\Equipment\Models\Equipment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Equipment
 */
final class EquipmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'equipment_type_id' => $this->equipment_type_id,
            'object_id' => $this->object_id,
            'name' => $this->name,
            'inventory_number' => $this->inventory_number,
            'serial_number' => $this->serial_number,
            'manufacturer' => $this->manufacturer,
            'model' => $this->model,
            'status' => $this->status->value,
            'object' => $this->whenLoaded('object', fn () => [
                'id' => $this->object->id,
                'name' => $this->object->name,
                'code' => $this->object->code,
            ]),
            'type' => $this->whenLoaded('type', fn () => [
                'id' => $this->type->id,
                'name' => $this->type->name,
            ]),
            'latest_verification' => $this->whenLoaded('latestVerification', fn () => $this->latestVerification === null ? null : [
                'id' => $this->latestVerification->id,
                'verified_at' => $this->latestVerification->verified_at?->toDateString(),
                'valid_until' => $this->latestVerification->valid_until?->toDateString(),
            ]),
            'latest_calibration' => $this->whenLoaded('latestCalibration', fn () => $this->latestCalibration === null ? null : [
                'id' => $this->latestCalibration->id,
                'calibrated_at' => $this->latestCalibration->calibrated_at?->toDateString(),
                'valid_until' => $this->latestCalibration->valid_until?->toDateString(),
            ]),
        ];
    }
}
