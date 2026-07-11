<?php

declare(strict_types=1);

namespace Tests\Feature\Pwa;

use App\Models\User;
use App\Modules\Auth\Enums\UserStatus;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class PwaScreensTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_open_pwa_screens(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::query()->create([
            'name' => 'Работник PWA',
            'email' => 'pwa@example.test',
            'password' => 'password',
            'status' => UserStatus::Active,
        ]);
        $user->assignRole(Role::findByName('Лаборант', 'web'));

        $this->actingAs($user);

        $this->get(route('pwa.tasks'))
            ->assertOk()
            ->assertSeeText('Мои задания НК');

        $this->get(route('pwa.lab-shift'))
            ->assertOk()
            ->assertSeeText('Смена лаборанта');

        $this->get(route('pwa.decoder'))
            ->assertOk()
            ->assertSeeText('Дешифровка');

        $this->get(route('pwa.control'))
            ->assertOk()
            ->assertSeeText('Контроль участка');
    }
}
