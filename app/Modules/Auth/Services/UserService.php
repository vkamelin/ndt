<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use App\Models\User;
use App\Modules\Auth\Enums\UserStatus;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class UserService
{
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
            subject: $user,
            event: 'user.roles.updated',
            before: ['roles' => $before],
            after: ['roles' => $user->getRoleNames()->values()->all()],
            actor: $actor,
            ipAddress: $ipAddress,
            userAgent: $userAgent,
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
            subject: $user,
            event: 'user.blocked',
            before: $before,
            after: $this->snapshot($user),
            actor: $actor,
            ipAddress: $ipAddress,
            userAgent: $userAgent,
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
            subject: $user,
            event: 'user.unblocked',
            before: $before,
            after: $this->snapshot($user),
            actor: $actor,
            ipAddress: $ipAddress,
            userAgent: $userAgent,
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

    /**
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     */
    private function recordAudit(
        User $subject,
        string $event,
        array $before,
        array $after,
        ?User $actor = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): void {
        DB::table('audit_logs')->insert([
            'actor_user_id' => $actor?->getKey(),
            'subject_type' => $subject::class,
            'subject_id' => $subject->getKey(),
            'event' => $event,
            'properties' => json_encode([
                'before' => $before,
                'after' => $after,
            ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'created_at' => now(),
        ]);
    }
}
