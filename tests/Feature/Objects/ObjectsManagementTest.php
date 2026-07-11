<?php

declare(strict_types=1);

namespace Tests\Feature\Objects;

use App\Models\User;
use App\Modules\Auth\Enums\UserStatus;
use App\Modules\Employees\Enums\EmployeeStatus;
use App\Modules\Employees\Models\Employee;
use App\Modules\Employees\Models\Position;
use App\Modules\Objects\Models\City;
use App\Modules\Objects\Models\NdtObject;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class ObjectsManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_city_and_object(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::query()->where('email', 'admin@example.test')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.cities.store'), [
                'name' => 'Екатеринбург',
                'comment' => 'Основной город',
                'is_active' => true,
            ])
            ->assertRedirect();

        $city = City::query()->where('name', 'Екатеринбург')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.objects.store'), [
                'city_id' => $city->id,
                'name' => 'Участок 1',
                'code' => 'U1',
                'comment' => 'Производственный участок',
                'is_active' => true,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('objects', [
            'city_id' => $city->id,
            'name' => 'Участок 1',
            'code' => 'U1',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.cities.index', ['search' => 'Екатеринбург']))
            ->assertOk()
            ->assertSee('Екатеринбург');
    }

    public function test_chief_cannot_view_or_edit_objects_anymore(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::query()->where('email', 'admin@example.test')->firstOrFail();
        $chief = User::factory()->create([
            'name' => 'Начальник участка',
            'email' => 'chief@example.test',
            'password' => 'password',
            'status' => UserStatus::Active,
        ]);
        $chief->assignRole(Role::findByName('Начальник участка', 'web'));

        $city = City::query()->create([
            'name' => 'Нижний Тагил',
            'is_active' => true,
            'comment' => null,
        ]);
        $objectA = NdtObject::query()->create([
            'city_id' => $city->id,
            'name' => 'Участок А',
            'code' => 'A',
            'is_active' => true,
            'comment' => null,
        ]);
        $objectB = NdtObject::query()->create([
            'city_id' => $city->id,
            'name' => 'Участок Б',
            'code' => 'B',
            'is_active' => true,
            'comment' => null,
        ]);
        $position = Position::query()->create([
            'name' => 'Инженер',
            'is_active' => true,
            'comment' => null,
        ]);

        Employee::query()->create([
            'object_id' => $objectA->id,
            'position_id' => $position->id,
            'last_name' => 'Иванов',
            'first_name' => 'Иван',
            'middle_name' => null,
            'phone' => null,
            'email' => null,
            'status' => EmployeeStatus::Active,
            'personnel_number' => '001',
        ])->users()->sync([$chief->id]);

        Employee::query()->create([
            'object_id' => $objectB->id,
            'position_id' => $position->id,
            'last_name' => 'Петров',
            'first_name' => 'Петр',
            'middle_name' => null,
            'phone' => null,
            'email' => null,
            'status' => EmployeeStatus::Active,
            'personnel_number' => '002',
        ]);

        $this->actingAs($chief)
            ->get(route('admin.objects.index', ['search' => 'Участок']))
            ->assertForbidden();

        $this->actingAs($chief)
            ->patch(route('admin.objects.update', $objectB), [
                'city_id' => $city->id,
                'name' => 'Участок Б',
                'code' => 'B',
                'comment' => null,
                'is_active' => true,
            ])
            ->assertForbidden();
    }
}
