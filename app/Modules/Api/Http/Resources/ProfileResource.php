<?php

declare(strict_types=1);

namespace App\Modules\Api\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
final class ProfileResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $employee = $this->primaryEmployee();

        return [
            'user' => new UserResource($this),
            'primary_employee' => $employee === null ? null : [
                'id' => $employee->id,
                'name' => trim(implode(' ', array_filter([
                    $employee->last_name,
                    $employee->first_name,
                    $employee->middle_name,
                ]))),
                'object_id' => $employee->object_id,
                'object' => $employee->relationLoaded('object') && $employee->object !== null ? [
                    'id' => $employee->object->id,
                    'name' => $employee->object->name,
                    'code' => $employee->object->code,
                    'city' => $employee->object->relationLoaded('city') && $employee->object->city !== null ? [
                        'id' => $employee->object->city->id,
                        'name' => $employee->object->city->name,
                    ] : null,
                ] : null,
            ],
            'roles' => $this->roles->pluck('name')->values()->all(),
            'permissions' => $this->permissions->pluck('name')->values()->all(),
        ];
    }
}
