<?php

declare(strict_types=1);

namespace Tests\Feature\Access;

use App\Modules\Access\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class RolePermissionSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_permission_seeder_creates_base_roles_and_permissions(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $this->assertDatabaseHas('roles', ['name' => 'Администратор системы', 'guard_name' => 'web']);
        $this->assertDatabaseHas('roles', ['name' => 'Начальник участка', 'guard_name' => 'web']);
        $this->assertDatabaseHas('roles', ['name' => 'Инженер НК / Дешифровщик', 'guard_name' => 'web']);
        $this->assertDatabaseHas('roles', ['name' => 'Дефектоскопист', 'guard_name' => 'web']);
        $this->assertDatabaseHas('roles', ['name' => 'Лаборант', 'guard_name' => 'web']);

        $this->assertDatabaseHas('permissions', ['name' => 'users.manage', 'guard_name' => 'web']);
        $this->assertDatabaseHas('permissions', ['name' => 'dashboard.view', 'guard_name' => 'web']);
        $this->assertDatabaseHas('permissions', ['name' => 'ndt_tasks.view_any', 'guard_name' => 'web']);
        $this->assertDatabaseHas('permissions', ['name' => 'ndt_tasks.manage', 'guard_name' => 'web']);
        $this->assertDatabaseHas('permissions', ['name' => 'weld_ndt_methods.manage', 'guard_name' => 'web']);

        $adminRole = Role::findByName('Администратор системы', 'web');

        $this->assertTrue($adminRole->hasPermissionTo('users.manage'));
        $this->assertTrue($adminRole->hasPermissionTo('roles.manage'));
        $this->assertTrue($adminRole->hasPermissionTo('ndt_tasks.manage'));
        $this->assertNotNull(Permission::findByName('dashboard.view', 'web'));
    }
}
