<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use App\Models\User;
use App\Modules\Audit\Concerns\RecordsAuditLogs;
use App\Modules\Audit\DTO\AuditData;
use App\Modules\Auth\Enums\UserStatus;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class UserService
{
    use RecordsAuditLogs;

    /**
     * @param  list<string>  $roleNames
     */
    public function syncRoles(User $user, array $roleNames, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): User
    {
        $roleNames = array_values(array_unique(array_filter($roleNames)));
        $before = $user->getRoleNames()->values()->all();

        $user->syncRoles($roleNames);
        $user->refresh();

        $this->recordAudit(
            AuditData::forModelChange(
                entityType: $user::class,
                entityId: $user->getKey(),
                operation: 'user.roles.updated',
                before: ['roles' => $before],
                after: ['roles' => $user->getRoleNames()->values()->all()],
                actor: $actor,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            ),
        );

        return $user;
    }

    public function block(User $user, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): User
    {
        if ($user->isBlocked()) {
            return $user;
        }

        $before = $this->snapshot($user);

        $user->forceFill([
            'status' => UserStatus::Blocked,
        ])->save();

        $user->refresh();

        $this->recordAudit(
            AuditData::forModelChange(
                entityType: $user::class,
                entityId: $user->getKey(),
                operation: 'user.blocked',
                before: $before,
                after: $this->snapshot($user),
                actor: $actor,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            ),
        );

        return $user;
    }

    public function unblock(User $user, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): User
    {
        if ($user->isActive()) {
            return $user;
        }

        $before = $this->snapshot($user);

        $user->forceFill([
            'status' => UserStatus::Active,
        ])->save();

        $user->refresh();

        $this->recordAudit(
            AuditData::forModelChange(
                entityType: $user::class,
                entityId: $user->getKey(),
                operation: 'user.unblocked',
                before: $before,
                after: $this->snapshot($user),
                actor: $actor,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            ),
        );

        return $user;
    }

    /**
     * @throws ValidationException
     */
    public function authenticate(string $email, string $password, bool $remember = false): User
    {
        $user = User::query()
            ->where('email', $email)
            ->first();

        if ($user instanceof User && $user->isBlocked()) {
            throw ValidationException::withMessages([
                'email' => 'Пользователь заблокирован.',
            ]);
        }

        if (! Auth::attempt(['email' => $email, 'password' => $password], $remember)) {
            throw ValidationException::withMessages([
                'email' => 'Неверный email или пароль.',
            ]);
        }

        $authenticatedUser = Auth::user();

        if (! $authenticatedUser instanceof User) {
            Auth::logout();

            throw new AuthenticationException();
        }

        return $authenticatedUser;
    }

    /**
     * Authenticate credentials for token-based clients without starting a session.
     */
    public function authenticateForToken(string $email, string $password): User
    {
        $user = User::query()
            ->where('email', $email)
            ->first();

        if ($user instanceof User && $user->isBlocked()) {
            throw ValidationException::withMessages([
                'email' => 'Пользователь заблокирован.',
            ]);
        }

        if (! $user instanceof User || ! Hash::check($password, (string) $user->getAuthPassword())) {
            throw ValidationException::withMessages([
                'email' => 'Неверный email или пароль.',
            ]);
        }

        return $user;
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(User $user): array
    {
        return [
            'id' => $user->getKey(),
            'name' => $user->name,
            'email' => $user->email,
            'status' => $user->status?->value ?? null,
            'roles' => $user->getRoleNames()->values()->all(),
        ];
    }
}
