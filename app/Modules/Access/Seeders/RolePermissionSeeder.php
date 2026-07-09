<?php

declare(strict_types=1);

namespace App\Modules\Access\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'dashboard.view',
            'profile.view',
            'audit_log.view_any',
            'users.view',
            'users.manage',
            'roles.view',
            'roles.manage',
        ];

        foreach ($permissions as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        $rolePermissions = [
            'Администратор системы' => $permissions,
            'Начальник участка' => [
                'dashboard.view',
                'profile.view',
            ],
            'Инженер НК / Дешифровщик' => [
                'dashboard.view',
                'profile.view',
            ],
            'Дефектоскопист' => [
                'dashboard.view',
                'profile.view',
            ],
            'Лаборант' => [
                'dashboard.view',
                'profile.view',
            ],
        ];

        foreach ($rolePermissions as $roleName => $permissionNames) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($permissionNames);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
