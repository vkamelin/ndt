<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Modules\Auth\Enums\UserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_see_login_page(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSeeText('Доступ к системе');
    }

    public function test_active_user_can_log_in_and_open_dashboard(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $user = User::factory()->create([
            'email' => 'worker@example.test',
            'password' => 'password',
            'status' => UserStatus::Active,
        ]);

        $user->assignRole(Role::findByName('Лаборант', 'web'));

        $this->post('/login', [
            'email' => 'worker@example.test',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);

        $this->get('/dashboard')
            ->assertOk()
            ->assertSeeText('Вы вошли как');
    }
}
