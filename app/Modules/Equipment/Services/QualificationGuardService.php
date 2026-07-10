<?php

declare(strict_types=1);

namespace App\Modules\Equipment\Services;

use App\Models\User;
use App\Modules\Employees\Enums\EmployeeStatus;
use App\Modules\Employees\Enums\QualificationMethod;
use App\Modules\Employees\Models\Employee;
use App\Modules\NdtTasks\Enums\NdtMethodCode;
use Illuminate\Validation\ValidationException;

final class QualificationGuardService
{
    public function ensureQualified(Employee $employee, QualificationMethod|NdtMethodCode|string $method, ?User $actor = null): void
    {
        if (! config('equipment.strict_qualification_guard')) {
            return;
        }

        if ($employee->status !== EmployeeStatus::Active) {
            throw ValidationException::withMessages([
                'assignee_employee_id' => 'Исполнитель должен быть активным сотрудником.',
            ]);
        }

        $methodValue = $method instanceof QualificationMethod || $method instanceof NdtMethodCode
            ? $method->value
            : $method;

        $qualification = $employee->qualifications()
            ->where('method', $methodValue)
            ->where(function ($query): void {
                $query->whereNull('valid_until')
                    ->orWhereDate('valid_until', '>=', today());
            })
            ->exists();

        if (! $qualification) {
            throw ValidationException::withMessages([
                'assignee_employee_id' => 'У исполнителя нет действующей квалификации по выбранному методу.',
            ]);
        }
    }
}
