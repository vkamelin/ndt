<?php

declare(strict_types=1);

namespace Tests\Feature\Inventory;

use App\Models\User;
use App\Modules\Auth\Enums\UserStatus;
use App\Modules\Employees\Enums\EmployeeStatus;
use App\Modules\Employees\Models\Employee;
use App\Modules\Employees\Models\Position;
use App\Modules\Objects\Models\City;
use App\Modules\Objects\Models\NdtObject;
use App\Modules\Shifts\Enums\ShiftType;
use App\Modules\Shifts\Models\Shift;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class InventoryWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_transactions_are_recorded_through_shift_routes(): void
    {
        $this->seed(DatabaseSeeder::class);

        $labUser = User::query()->create([
            'name' => 'Лаборант',
            'email' => 'inventory-lab@example.test',
            'password' => 'password',
            'status' => UserStatus::Active,
        ]);
        $labUser->assignRole(Role::findByName('Лаборант', 'web'));

        $city = City::query()->create([
            'name' => 'Казань',
            'is_active' => true,
            'comment' => null,
        ]);
        $object = NdtObject::query()->create([
            'city_id' => $city->id,
            'name' => 'Участок инвентаря',
            'code' => 'INV',
            'is_active' => true,
            'comment' => null,
        ]);
        $position = Position::query()->create([
            'name' => 'Лаборант',
            'is_active' => true,
            'comment' => null,
        ]);
        $employee = Employee::query()->create([
            'object_id' => $object->id,
            'position_id' => $position->id,
            'last_name' => 'Кузнецова',
            'first_name' => 'Ольга',
            'middle_name' => null,
            'phone' => null,
            'email' => null,
            'status' => EmployeeStatus::Active,
            'personnel_number' => '3001',
        ]);
        $employee->users()->sync([$labUser->id]);

        $this->actingAs($labUser)
            ->post(route('admin.shifts.store'), [
                'employee_id' => $employee->id,
                'type' => ShiftType::Lab->value,
                'comment' => 'Смена для инвентаря',
            ])
            ->assertRedirect();

        $shift = Shift::query()->where('employee_id', $employee->id)->firstOrFail();

        $this->actingAs($labUser)
            ->post(route('admin.shifts.lab.film-transactions.store', $shift), [
                'rt_film_id' => null,
                'quantity' => 5,
                'transacted_at' => now()->toDateTimeString(),
                'comment' => 'Приход пленки',
            ])
            ->assertRedirect();

        $this->actingAs($labUser)
            ->post(route('admin.shifts.lab.chemical-transactions.store', $shift), [
                'chemical_type_id' => null,
                'quantity' => 3,
                'transacted_at' => now()->toDateTimeString(),
                'comment' => 'Приход химии',
            ])
            ->assertRedirect();

        $this->actingAs($labUser)
            ->post(route('admin.shifts.lab.chemical-requests.store', $shift), [
                'chemical_type_id' => null,
                'quantity' => 2,
                'requested_at' => now()->toDateTimeString(),
                'comment' => 'Нужен запас',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('film_inventory_transactions', [
            'shift_id' => $shift->id,
            'quantity' => 5,
            'operation' => 'received',
        ]);
        $this->assertDatabaseHas('chemical_inventory_transactions', [
            'shift_id' => $shift->id,
            'quantity' => 3,
            'operation' => 'received',
        ]);
        $this->assertDatabaseHas('chemical_requests', [
            'shift_id' => $shift->id,
            'quantity' => 2,
        ]);
    }
}
