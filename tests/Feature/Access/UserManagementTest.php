<?php

declare(strict_types=1);

namespace Tests\Feature\Access;

use App\Models\User;
use App\Modules\Auth\Enums\UserStatus;
use App\Modules\Employees\Enums\EmployeeStatus;
use App\Modules\Employees\Models\Employee;
use App\Modules\Employees\Models\Position;
use App\Modules\Objects\Models\City;
use App\Modules\Objects\Models\NdtObject;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_user_roles_and_status(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::query()->where('email', 'admin@example.test')->firstOrFail();
        $city = City::query()->create([
            'name' => 'Тюмень',
            'is_active' => true,
            'comment' => null,
        ]);
        $object = NdtObject::query()->create([
            'city_id' => $city->id,
            'name' => 'Тюменский участок',
            'code' => null,
            'is_active' => true,
            'comment' => null,
        ]);
        $position = Position::query()->create([
            'name' => 'Лаборант',
            'is_active' => true,
            'comment' => null,
        ]);
        $employee = Employee::query()->create([
            'object_id' => $object->id,
            'position_id' => $position->id,
            'last_name' => 'Смирнов',
            'first_name' => 'Сергей',
            'middle_name' => null,
            'phone' => '+7 900 000-00-00',
            'email' => 'employee@example.test',
            'status' => EmployeeStatus::Active,
            'personnel_number' => '123',
        ]);
        $linkedEmployee = Employee::query()->create([
            'object_id' => $object->id,
            'position_id' => $position->id,
            'last_name' => 'Петров',
            'first_name' => 'Павел',
            'middle_name' => null,
            'phone' => null,
            'email' => null,
            'status' => EmployeeStatus::Active,
            'personnel_number' => '124',
        ]);
        $conflictUser = User::factory()->create([
            'email' => 'conflict@example.test',
            'password' => 'password',
            'status' => UserStatus::Active,
        ]);
        $linkedEmployee->users()->sync([$conflictUser->id]);

        $user = User::factory()->create([
            'email' => 'employee@example.test',
            'password' => 'password',
            'status' => UserStatus::Active,
        ]);
        $user->assignRole(Role::findByName('Лаборант', 'web'));

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertOk()
            ->assertSeeText('Пользователи')
            ->assertSeeText('Сотрудники')
            ->assertSeeText('employee@example.test')
            ->assertDontSeeText('Сохранить роли')
            ->assertDontSeeText('Заблокировать');

        $this->actingAs($admin)
            ->get(route('admin.users.show', $user))
            ->assertOk()
            ->assertSeeText('Карточка пользователя')
            ->assertSeeText('Данные пользователя')
            ->assertSeeText('Связанный сотрудник')
            ->assertDontSeeText('Связанные сотрудники')
            ->assertSeeText('Сохранить роли');

        $this->actingAs($admin)
            ->patch(route('admin.users.update', $user), [
                'name' => 'Новое имя',
                'email' => 'updated@example.test',
                'status' => UserStatus::Active->value,
                'employee_id' => $employee->id,
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertRedirect();

        $user->refresh();
        $this->assertSame('Новое имя', $user->name);
        $this->assertSame('updated@example.test', $user->email);
        $this->assertTrue(Hash::check('new-password', $user->getAuthPassword()));
        $this->assertSame($employee->id, $user->primaryEmployee()?->id);
        $this->assertDatabaseHas('audit_logs', [
            'actor_user_id' => $admin->id,
            'subject_id' => $user->id,
            'event' => 'user.profile.updated',
        ]);
        $this->assertDatabaseHas('employee_user', [
            'user_id' => $user->id,
            'employee_id' => $employee->id,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.users.update', $user), [
                'name' => 'Новое имя',
                'email' => 'updated@example.test',
                'status' => UserStatus::Active->value,
                'employee_id' => $linkedEmployee->id,
                'password' => '',
                'password_confirmation' => '',
            ])
            ->assertSessionHasErrors('employee_id');

        $user->refresh();
        $this->assertSame($employee->id, $user->primaryEmployee()?->id);

        $this->actingAs($admin)
            ->patch(route('admin.users.roles.update', $user), [
                'roles' => ['Дефектоскопист'],
            ])
            ->assertRedirect();

        $user->refresh();
        $this->assertTrue($user->hasRole('Дефектоскопист'));
        $this->assertDatabaseHas('audit_logs', [
            'actor_user_id' => $admin->id,
            'subject_id' => $user->id,
            'event' => 'user.roles.updated',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.users.block', $user))
            ->assertRedirect();

        $user->refresh();
        $this->assertTrue($user->status->isBlocked());
        $this->assertDatabaseHas('audit_logs', [
            'actor_user_id' => $admin->id,
            'subject_id' => $user->id,
            'event' => 'user.blocked',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.users.unblock', $user))
            ->assertRedirect();

        $user->refresh();
        $this->assertTrue($user->status->isActive());
        $this->assertDatabaseHas('audit_logs', [
            'actor_user_id' => $admin->id,
            'subject_id' => $user->id,
            'event' => 'user.unblocked',
        ]);
    }
}
