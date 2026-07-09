<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Modules\Auth\Enums\UserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class BlockedUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_blocked_user_cannot_log_in(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        User::factory()->create([
            'email' => 'blocked@example.test',
            'password' => 'password',
            'status' => UserStatus::Blocked,
        ])->assignRole(Role::findByName('Лаборант', 'web'));

        $this->from('/login')
            ->post('/login', [
                'email' => 'blocked@example.test',
                'password' => 'password',
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');
    }

    public function test_blocked_user_is_logged_out_by_middleware(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $user = User::factory()->create([
            'email' => 'blocked2@example.test',
            'password' => 'password',
            'status' => UserStatus::Blocked,
        ]);
        $user->assignRole(Role::findByName('Лаборант', 'web'));

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
