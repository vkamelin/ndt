<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use App\Models\User;
use App\Modules\Audit\Concerns\RecordsAuditLogs;
use App\Modules\Audit\DTO\AuditData;
use App\Modules\Auth\Enums\UserStatus;
use App\Modules\Employees\Models\Employee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class UserService
{
    use RecordsAuditLogs;

    /**
     * @param  array{name: string, email: string, status: string, password?: string|null, employee_id?: int|null}  $data
     */
    public function update(User $user, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): User
    {
        return DB::transaction(function () use ($user, $data, $actor, $ipAddress, $userAgent): User {
            $before = $this->snapshot($user);

            $payload = [
                'name' => $data['name'],
                'email' => $data['email'],
                'status' => UserStatus::from($data['status']),
            ];

            if (isset($data['password']) && $data['password'] !== null && $data['password'] !== '') {
                $payload['password'] = $data['password'];
            }

            $user->fill($payload)->save();
            $this->syncEmployee($user, $data['employee_id'] ?? null);
            $user->refresh();

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: $user::class,
                    entityId: $user->getKey(),
                    operation: 'user.profile.updated',
                    before: $before,
                    after: $this->snapshot($user),
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $user;
        });
    }

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

    /**
     * @throws ValidationException
     */
    public function syncEmployee(User $user, ?int $employeeId): User
    {
        $currentEmployeeId = $user->employees()->value('employees.id');
        $currentEmployeeId = $currentEmployeeId === null ? null : (int) $currentEmployeeId;

        if ($currentEmployeeId === $employeeId) {
            return $user;
        }

        if ($employeeId !== null) {
            $conflict = Employee::query()
                ->whereKey($employeeId)
                ->whereHas('users', function (Builder $query) use ($user): void {
                    $query->whereKeyNot($user->getKey());
                })
                ->exists();

            if ($conflict) {
                throw ValidationException::withMessages([
                    'employee_id' => 'Выбранный сотрудник уже связан с другим пользователем.',
                ]);
            }
        }

        $user->employees()->sync($employeeId === null ? [] : [$employeeId]);
        $user->refresh();

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

            throw new AuthenticationException;
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
            'employee_id' => $user->primaryEmployee()?->getKey(),
            'roles' => $user->getRoleNames()->values()->all(),
        ];
    }
}
