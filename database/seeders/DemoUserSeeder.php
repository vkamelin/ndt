<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Auth\Enums\UserStatus;
use App\Modules\Employees\Enums\EmployeeStatus;
use App\Modules\Employees\Models\Employee;
use App\Modules\Employees\Models\Position;
use App\Modules\Objects\Models\City;
use App\Modules\Objects\Models\NdtObject;
use Illuminate\Database\Seeder;

final class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $roleUsers = [
            [
                'name' => 'Администратор системы',
                'email' => 'admin@example.test',
                'role' => 'Администратор системы',
            ],
            [
                'name' => 'Начальник участка',
                'email' => 'chief@example.test',
                'role' => 'Начальник участка',
                'position' => 'Начальник участка',
                'city' => 'Орск',
                'object' => 'Орский участок',
                'personnel_number' => '1001',
                'employee_last_name' => 'Иванов',
                'employee_first_name' => 'Иван',
            ],
            [
                'name' => 'Инженер НК / Дешифровщик',
                'email' => 'engineer@example.test',
                'role' => 'Инженер НК / Дешифровщик',
                'position' => 'Инженер НК / Дешифровщик',
                'city' => 'Комсомольск-на-Амуре',
                'object' => 'Комсомольский участок',
                'personnel_number' => '1002',
                'employee_last_name' => 'Петров',
                'employee_first_name' => 'Петр',
            ],
            [
                'name' => 'Дефектоскопист',
                'email' => 'defectoscopist@example.test',
                'role' => 'Дефектоскопист',
                'position' => 'Дефектоскопист',
                'city' => 'Орск',
                'object' => 'Орский участок',
                'personnel_number' => '1003',
                'employee_last_name' => 'Сидоров',
                'employee_first_name' => 'Семен',
            ],
            [
                'name' => 'Лаборант',
                'email' => 'laborant@example.test',
                'role' => 'Лаборант',
                'position' => 'Лаборант',
                'city' => 'Комсомольск-на-Амуре',
                'object' => 'Комсомольский участок',
                'personnel_number' => '1004',
                'employee_last_name' => 'Кузнецова',
                'employee_first_name' => 'Анна',
            ],
        ];

        foreach ($roleUsers as $definition) {
            $user = User::query()->updateOrCreate(
                ['email' => $definition['email']],
                [
                    'name' => $definition['name'],
                    'password' => 'password',
                    'status' => UserStatus::Active,
                ],
            );

            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();

            $user->syncRoles([$definition['role']]);

            if (isset($definition['position'], $definition['city'], $definition['object'], $definition['personnel_number'])) {
                $employee = $this->seedEmployee($definition);
                $user->employees()->sync([$employee->id]);
            }
        }
    }

    /**
     * @param array{
     *     position: string,
     *     city: string,
     *     object: string,
     *     personnel_number: string,
     *     employee_last_name: string,
     *     employee_first_name: string
     * } $definition
     */
    private function seedEmployee(array $definition): Employee
    {
        $position = Position::query()->where('name', $definition['position'])->firstOrFail();
        $city = City::query()->where('name', $definition['city'])->firstOrFail();
        $object = NdtObject::query()
            ->where('city_id', $city->id)
            ->where('name', $definition['object'])
            ->firstOrFail();

        return Employee::query()->updateOrCreate(
            ['personnel_number' => $definition['personnel_number']],
            [
                'object_id' => $object->id,
                'position_id' => $position->id,
                'last_name' => $definition['employee_last_name'],
                'first_name' => $definition['employee_first_name'],
                'middle_name' => null,
                'phone' => null,
                'email' => $definition['personnel_number'] . '@example.test',
                'status' => EmployeeStatus::Active,
            ],
        );
    }
}
