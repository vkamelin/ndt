<?php

declare(strict_types=1);

namespace Tests\Feature\Access;

use App\Models\User;
use App\Modules\Auth\Enums\UserStatus;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_user_roles_and_status(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::query()->where('email', 'admin@example.test')->firstOrFail();
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
            ->assertSeeText('employee@example.test')
            ->assertDontSeeText('Сохранить роли')
            ->assertDontSeeText('Заблокировать');

        $this->actingAs($admin)
            ->get(route('admin.users.show', $user))
            ->assertOk()
            ->assertSeeText('Карточка пользователя')
            ->assertSeeText('Сохранить роли');

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
