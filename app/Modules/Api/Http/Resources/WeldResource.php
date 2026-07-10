<?php

declare(strict_types=1);

namespace App\Modules\Api\Http\Resources;

use App\Modules\Welds\Models\Weld;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Weld
 */
final class WeldResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'object_id' => $this->object_id,
            'weld_number' => $this->weld_number,
            'status' => $this->status->value,
            'diameter' => $this->diameter,
            'thickness' => $this->thickness,
            'pwht' => $this->pwht,
            'object' => $this->whenLoaded('object', function () {
                return [
                    'id' => $this->object->id,
                    'name' => $this->object->name,
                    'code' => $this->object->code,
                    'city' => $this->object->relationLoaded('city') && $this->object->city !== null ? [
                        'id' => $this->object->city->id,
                        'name' => $this->object->city->name,
                    ] : null,
                ];
            }),
            'methods' => $this->whenLoaded('ndtMethods', fn (): array => $this->ndtMethods->map(static fn ($method): array => [
                'id' => $method->id,
                'code' => $method->code->value,
                'name' => $method->name,
            ])->values()->all(), []),
        ];
    }
}
