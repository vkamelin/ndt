<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Employees\Models\Employee;
use App\Modules\Objects\Models\City;
use App\Modules\Objects\Models\NdtObject;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_base_users_and_reference_data(): void
    {
        $this->seed(DatabaseSeeder::class);

        foreach ([
            'admin@example.test' => 'Администратор системы',
            'chief@example.test' => 'Начальник участка',
            'engineer@example.test' => 'Инженер НК / Дешифровщик',
            'defectoscopist@example.test' => 'Дефектоскопист',
            'laborant@example.test' => 'Лаборант',
        ] as $email => $roleName) {
            $user = User::query()->where('email', $email)->firstOrFail();

            $this->assertTrue($user->hasRole($roleName));
            $this->assertTrue($user->status->isActive());
        }

        $this->assertDatabaseHas('cities', ['name' => 'Орск']);
        $this->assertDatabaseHas('cities', ['name' => 'Комсомольск-на-Амуре']);
        $this->assertDatabaseHas('positions', ['name' => 'Начальник участка']);
        $this->assertDatabaseHas('positions', ['name' => 'Лаборант']);
        $this->assertDatabaseHas('materials', ['name' => 'Сталь 20']);
        $this->assertDatabaseHas('welding_processes', ['name' => 'РД']);
        $this->assertDatabaseHas('result_statuses', ['name' => 'Положительный']);
        $this->assertDatabaseHas('register_types', ['name' => 'Передача пленок']);
        $this->assertDatabaseHas('act_types', ['name' => 'Акт ВР']);
        $this->assertDatabaseHas('film_types', ['name' => 'Рентгеновская пленка']);

        $orsk = City::query()->where('name', 'Орск')->firstOrFail();
        $komsomolsk = City::query()->where('name', 'Комсомольск-на-Амуре')->firstOrFail();

        $this->assertDatabaseHas('objects', [
            'city_id' => $orsk->id,
            'name' => 'Орский участок',
        ]);
        $this->assertDatabaseHas('objects', [
            'city_id' => $komsomolsk->id,
            'name' => 'Комсомольский участок',
        ]);

        $this->assertSame(4, Employee::query()->count());
        $this->assertDatabaseHas('employee_user', ['user_id' => User::query()->where('email', 'chief@example.test')->value('id')]);
        $this->assertDatabaseHas('employee_user', ['user_id' => User::query()->where('email', 'laborant@example.test')->value('id')]);
    }
}
