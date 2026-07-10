<?php

declare(strict_types=1);

namespace App\Modules\Api\Http\Controllers;

use App\Models\User;
use App\Modules\Api\Http\Resources\ProfileResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ProfileController extends ApiController
{
    public function show(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user->loadMissing('roles', 'permissions', 'employees.object.city');

        return $this->success(new ProfileResource($user));
    }
}
