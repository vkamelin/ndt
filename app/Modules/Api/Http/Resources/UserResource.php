<?php

declare(strict_types=1);

namespace App\Modules\Api\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
final class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'status' => $this->status?->value,
            'roles' => $this->whenLoaded('roles', fn (): array => $this->roles->pluck('name')->values()->all(), []),
            'permissions' => $this->whenLoaded('permissions', fn (): array => $this->permissions->pluck('name')->values()->all(), []),
        ];
    }
}
