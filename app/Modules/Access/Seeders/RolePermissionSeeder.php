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
            'cities.view_any',
            'cities.manage',
            'objects.view_any',
            'objects.manage',
            'employees.view_any',
            'employees.manage',
            'positions.manage',
            'directories.view_any',
            'directories.manage',
            'organizations.view_any',
            'organizations.manage',
            'welds.view_any',
            'welds.manage',
            'ndt_requests.view_any',
            'ndt_requests.manage',
        ];

        foreach ($permissions as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        $rolePermissions = [
            'Администратор системы' => $permissions,
            'Начальник участка' => [
                'dashboard.view',
                'profile.view',
                'objects.view_any',
                'employees.view_any',
                'organizations.view_any',
                'organizations.manage',
                'welds.view_any',
                'welds.manage',
                'ndt_requests.view_any',
                'ndt_requests.manage',
            ],
            'Инженер НК / Дешифровщик' => [
                'dashboard.view',
                'profile.view',
                'organizations.view_any',
                'welds.view_any',
                'ndt_requests.view_any',
            ],
            'Дефектоскопист' => [
                'dashboard.view',
                'profile.view',
                'organizations.view_any',
                'welds.view_any',
                'ndt_requests.view_any',
            ],
            'Лаборант' => [
                'dashboard.view',
                'profile.view',
                'organizations.view_any',
                'welds.view_any',
                'ndt_requests.view_any',
            ],
        ];

        foreach ($rolePermissions as $roleName => $permissionNames) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($permissionNames);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
