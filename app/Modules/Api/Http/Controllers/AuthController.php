<?php

declare(strict_types=1);

namespace App\Modules\Api\Http\Controllers;

use App\Modules\Api\Http\Requests\LoginRequest;
use App\Modules\Api\Http\Resources\ProfileResource;
use App\Modules\Auth\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AuthController extends ApiController
{
    public function login(LoginRequest $request, UserService $users): JsonResponse
    {
        $user = $users->authenticateForToken(
            email: $request->string('email')->toString(),
            password: $request->string('password')->toString(),
        );

        $tokenName = $request->string('device_name')->toString();
        $tokenName = $tokenName !== '' ? $tokenName : 'mobile';

        $user->loadMissing('roles', 'permissions', 'employees.object.city');

        $token = $user->createToken($tokenName);

        return $this->success([
            'profile' => new ProfileResource($user),
            'token' => $token->plainTextToken,
            'token_name' => $tokenName,
            'token_type' => 'Bearer',
        ], 'Вход выполнен.');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return $this->success([
            'logged_out' => true,
        ], 'Выход выполнен.');
    }
}
