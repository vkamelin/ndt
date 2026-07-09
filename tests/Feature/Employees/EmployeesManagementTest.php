<?php

declare(strict_types=1);

namespace Tests\Feature\Employees;

use App\Models\User;
use App\Modules\Employees\Enums\EmployeeStatus;
use App\Modules\Employees\Enums\QualificationMethod;
use App\Modules\Employees\Models\Employee;
use App\Modules\Employees\Models\Position;
use App\Modules\Objects\Models\City;
use App\Modules\Objects\Models\NdtObject;
use App\Modules\Objects\Models\NdtObject as ObjectModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class EmployeesManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_employee_update_object_and_add_qualification(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $admin = User::query()->where('email', 'admin@example.test')->firstOrFail();
        $city = City::query()->create([
            'name' => 'Челябинск',
            'is_active' => true,
            'comment' => null,
        ]);
        $objectA = NdtObject::query()->create([
            'city_id' => $city->id,
            'name' => 'Объект А',
            'code' => null,
            'is_active' => true,
            'comment' => null,
        ]);
        $objectB = NdtObject::query()->create([
            'city_id' => $city->id,
            'name' => 'Объект Б',
            'code' => null,
            'is_active' => true,
            'comment' => null,
        ]);
        $position = Position::query()->create([
            'name' => 'Лаборант',
            'is_active' => true,
            'comment' => null,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.employees.store'), [
                'object_id' => $objectA->id,
                'position_id' => $position->id,
                'user_id' => null,
                'last_name' => 'Смирнов',
                'first_name' => 'Сергей',
                'middle_name' => null,
                'phone' => '+7 900 000-00-00',
                'email' => 'employee@example.test',
                'personnel_number' => '123',
                'status' => EmployeeStatus::Active->value,
            ])
            ->assertRedirect();

        $employee = Employee::query()->where('personnel_number', '123')->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('admin.employees.update', $employee), [
                'object_id' => $objectB->id,
                'position_id' => $position->id,
                'user_id' => null,
                'last_name' => 'Смирнов',
                'first_name' => 'Сергей',
                'middle_name' => null,
                'phone' => '+7 900 000-00-00',
                'email' => 'employee@example.test',
                'personnel_number' => '123',
                'status' => EmployeeStatus::Active->value,
            ])
            ->assertRedirect();

        $employee->refresh();
        $this->assertSame($objectB->id, $employee->object_id);
        $this->assertDatabaseHas('audit_logs', [
            'subject_type' => Employee::class,
            'subject_id' => $employee->id,
            'event' => 'employee.updated',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.employees.qualifications.store', $employee), [
                'method' => QualificationMethod::RK->value,
                'valid_until' => now()->addYear()->toDateString(),
                'comment' => 'Первичная аттестация',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('employee_qualifications', [
            'employee_id' => $employee->id,
            'method' => QualificationMethod::RK->value,
        ]);
    }

    public function test_chief_sees_only_employees_of_assigned_object(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $chief = User::factory()->create([
            'name' => 'Начальник участка',
            'email' => 'chief-employee@example.test',
            'password' => 'password',
            'status' => \App\Modules\Auth\Enums\UserStatus::Active,
        ]);
        $chief->assignRole(Role::findByName('Начальник участка', 'web'));

        $city = City::query()->create([
            'name' => 'Пермь',
            'is_active' => true,
            'comment' => null,
        ]);
        $objectA = ObjectModel::query()->create([
            'city_id' => $city->id,
            'name' => 'Участок А',
            'code' => null,
            'is_active' => true,
            'comment' => null,
        ]);
        $objectB = ObjectModel::query()->create([
            'city_id' => $city->id,
            'name' => 'Участок Б',
            'code' => null,
            'is_active' => true,
            'comment' => null,
        ]);
        $position = Position::query()->create([
            'name' => 'Дефектоскопист',
            'is_active' => true,
            'comment' => null,
        ]);

        $chiefEmployee = Employee::query()->create([
            'object_id' => $objectA->id,
            'position_id' => $position->id,
            'last_name' => 'Иванов',
            'first_name' => 'Иван',
            'middle_name' => null,
            'phone' => null,
            'email' => null,
            'status' => EmployeeStatus::Active,
            'personnel_number' => '100',
        ]);
        $chiefEmployee->users()->sync([$chief->id]);

        Employee::query()->create([
            'object_id' => $objectB->id,
            'position_id' => $position->id,
            'last_name' => 'Петров',
            'first_name' => 'Петр',
            'middle_name' => null,
            'phone' => null,
            'email' => null,
            'status' => EmployeeStatus::Active,
            'personnel_number' => '101',
        ]);

        $this->actingAs($chief)
            ->get(route('admin.employees.index'))
            ->assertOk()
            ->assertSeeText('Иванов')
            ->assertDontSeeText('Петров');
    }
}
