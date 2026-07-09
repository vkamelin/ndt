<?php

declare(strict_types=1);

namespace App\Modules\Employees\Services;

use App\Models\User;
use App\Modules\Audit\Concerns\RecordsAuditLogs;
use App\Modules\Audit\DTO\AuditData;
use App\Modules\Employees\Enums\EmployeeStatus;
use App\Modules\Employees\Enums\QualificationMethod;
use App\Modules\Employees\Models\Employee;
use App\Modules\Employees\Models\EmployeeQualification;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

final class EmployeeService
{
    use RecordsAuditLogs;

    /**
     * @param  array{object_id: int, position_id: int, user_id?: int|null, last_name: string, first_name: string, middle_name?: string|null, phone?: string|null, email?: string|null, personnel_number?: string|null, status: string}  $data
     */
    public function create(array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): Employee
    {
        return DB::transaction(function () use ($data, $actor, $ipAddress, $userAgent): Employee {
            $employee = Employee::query()->create($this->normalize($data));
            $this->syncUser($employee, $data['user_id'] ?? null, $actor, $ipAddress, $userAgent);

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: Employee::class,
                    entityId: $employee->getKey(),
                    operation: 'employee.created',
                    after: $this->snapshot($employee),
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $employee;
        });
    }

    /**
     * @param  array{object_id?: int, position_id?: int, user_id?: int|null, last_name?: string, first_name?: string, middle_name?: string|null, phone?: string|null, email?: string|null, personnel_number?: string|null, status?: string}  $data
     */
    public function update(Employee $employee, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): Employee
    {
        return DB::transaction(function () use ($employee, $data, $actor, $ipAddress, $userAgent): Employee {
            $before = $this->snapshot($employee);
            $employee->fill($this->normalize($data, false))->save();
            $employee->refresh();

            $this->syncUser($employee, $data['user_id'] ?? null, $actor, $ipAddress, $userAgent);

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: Employee::class,
                    entityId: $employee->getKey(),
                    operation: 'employee.updated',
                    before: $before,
                    after: $this->snapshot($employee),
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $employee;
        });
    }

    public function deactivate(Employee $employee, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): Employee
    {
        if ($employee->status === EmployeeStatus::Inactive) {
            return $employee;
        }

        return $this->update($employee, ['status' => EmployeeStatus::Inactive->value], $actor, $ipAddress, $userAgent);
    }

    /**
     * @param  array{method: string, valid_until?: string|null, comment?: string|null}  $data
     */
    public function addQualification(Employee $employee, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): EmployeeQualification
    {
        return DB::transaction(function () use ($employee, $data, $actor, $ipAddress, $userAgent): EmployeeQualification {
            $qualification = $employee->qualifications()->create([
                'method' => QualificationMethod::from($data['method']),
                'valid_until' => $data['valid_until'] ?? null,
                'comment' => $data['comment'] ?? null,
            ]);

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: EmployeeQualification::class,
                    entityId: $qualification->getKey(),
                    operation: 'employee.qualification.created',
                    after: $this->qualificationSnapshot($qualification),
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $qualification;
        });
    }

    /**
     * @param  array{method?: string, valid_until?: string|null, comment?: string|null}  $data
     */
    public function updateQualification(EmployeeQualification $qualification, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): EmployeeQualification
    {
        return DB::transaction(function () use ($qualification, $data, $actor, $ipAddress, $userAgent): EmployeeQualification {
            $before = $this->qualificationSnapshot($qualification);
            $qualification->fill(array_filter([
                'method' => isset($data['method']) ? QualificationMethod::from($data['method']) : null,
                'valid_until' => $data['valid_until'] ?? null,
                'comment' => $data['comment'] ?? null,
            ], static fn (mixed $value): bool => $value !== null))->save();
            $qualification->refresh();

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: EmployeeQualification::class,
                    entityId: $qualification->getKey(),
                    operation: 'employee.qualification.updated',
                    before: $before,
                    after: $this->qualificationSnapshot($qualification),
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $qualification;
        });
    }

    public function removeQualification(EmployeeQualification $qualification, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): void
    {
        DB::transaction(function () use ($qualification, $actor, $ipAddress, $userAgent): void {
            $before = $this->qualificationSnapshot($qualification);
            $qualification->delete();

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: EmployeeQualification::class,
                    entityId: $before['id'],
                    operation: 'employee.qualification.deleted',
                    before: $before,
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );
        });
    }

    public function syncUser(Employee $employee, ?int $userId, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): Employee
    {
        return DB::transaction(function () use ($employee, $userId, $actor, $ipAddress, $userAgent): Employee {
            $currentUserId = $employee->users()->value('users.id');

            if ($currentUserId === $userId) {
                return $employee;
            }

            if ($userId !== null) {
                $conflict = User::query()
                    ->whereKey($userId)
                    ->whereHas('employees', function ($query) use ($employee): void {
                        $query->whereKeyNot($employee->getKey());
                    })
                    ->exists();

                if ($conflict) {
                    throw ValidationException::withMessages([
                        'user_id' => 'Выбранный пользователь уже связан с другим сотрудником.',
                    ]);
                }
            }

            $before = [
                'employee_id' => $employee->getKey(),
                'user_id' => $currentUserId,
            ];

            $employee->users()->sync($userId === null ? [] : [$userId]);
            $employee->refresh();

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: Employee::class,
                    entityId: $employee->getKey(),
                    operation: 'employee.user.updated',
                    before: $before,
                    after: [
                        'employee_id' => $employee->getKey(),
                        'user_id' => $employee->users()->value('users.id'),
                    ],
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $employee;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalize(array $data, bool $creating = true): array
    {
        if ($creating && isset($data['user_id']) && $data['user_id'] === '') {
            $data['user_id'] = null;
        }

        $data['status'] = isset($data['status']) && $data['status'] === EmployeeStatus::Inactive->value
            ? EmployeeStatus::Inactive
            : EmployeeStatus::Active;

        return array_filter($data, static fn (mixed $value): bool => $value !== null);
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(Employee $employee): array
    {
        return [
            'id' => $employee->getKey(),
            'object_id' => $employee->object_id,
            'position_id' => $employee->position_id,
            'user_id' => $employee->users()->value('users.id'),
            'last_name' => $employee->last_name,
            'first_name' => $employee->first_name,
            'middle_name' => $employee->middle_name,
            'phone' => $employee->phone,
            'email' => $employee->email,
            'status' => $employee->status instanceof EmployeeStatus ? $employee->status->value : $employee->status,
            'personnel_number' => $employee->personnel_number,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function qualificationSnapshot(EmployeeQualification $qualification): array
    {
        return [
            'id' => $qualification->getKey(),
            'employee_id' => $qualification->employee_id,
            'method' => $qualification->method instanceof QualificationMethod ? $qualification->method->value : $qualification->method,
            'valid_until' => $qualification->valid_until?->toDateString(),
            'comment' => $qualification->comment,
        ];
    }
}
