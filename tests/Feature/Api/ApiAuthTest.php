<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use App\Modules\Auth\Enums\UserStatus;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_read_profile(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::query()->create([
            'name' => 'Лаборант',
            'email' => 'api-lab@example.test',
            'password' => 'password',
            'status' => UserStatus::Active,
        ]);
        $user->assignRole(Role::findByName('Лаборант', 'web'));

        $response = $this->postJson('/api/auth/login', [
            'email' => 'api-lab@example.test',
            'password' => 'password',
            'device_name' => 'iPhone',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('data.profile.user.email', 'api-lab@example.test');

        $token = $response->json('data.token');

        $this->getJson('/api/profile', [
            'Authorization' => 'Bearer '.$token,
        ])->assertOk()
            ->assertJsonPath('data.user.email', 'api-lab@example.test');

        $this->postJson('/api/auth/logout', [], [
            'Authorization' => 'Bearer '.$token,
        ])->assertOk()
            ->assertJsonPath('data.logged_out', true);
    }

    public function test_invalid_credentials_return_unified_error_payload(): void
    {
        $this->postJson('/api/auth/login', [
            'email' => 'missing@example.test',
            'password' => 'wrong',
        ])->assertStatus(422)
            ->assertJsonPath('message', 'Ошибка валидации.')
            ->assertJsonStructure(['message', 'errors' => ['email']]);
    }
}
