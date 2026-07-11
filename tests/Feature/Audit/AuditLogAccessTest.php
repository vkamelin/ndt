<?php

declare(strict_types=1);

namespace Tests\Feature\Audit;

use App\Models\User;
use App\Modules\Auth\Enums\UserStatus;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class AuditLogAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_audit_log_page(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::query()->where('email', 'admin@example.test')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/audit-logs')
            ->assertOk()
            ->assertSeeText('Журнал аудита');
    }

    public function test_user_without_permission_cannot_open_audit_log_page(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::factory()->create([
            'email' => 'auditorless@example.test',
            'password' => 'password',
            'status' => UserStatus::Active,
        ]);
        $user->assignRole(Role::findByName('Лаборант', 'web'));

        $this->actingAs($user)
            ->get('/admin/audit-logs')
            ->assertForbidden();
    }
}
