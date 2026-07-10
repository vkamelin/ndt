<?php

declare(strict_types=1);

namespace Tests\Feature\Shifts;

use App\Models\User;
use App\Modules\Auth\Enums\UserStatus;
use App\Modules\Employees\Enums\EmployeeStatus;
use App\Modules\Employees\Models\Employee;
use App\Modules\Employees\Models\Position;
use App\Modules\NdtResults\Enums\NdtResultStatus;
use App\Modules\NdtResults\Models\NdtResult;
use App\Modules\NdtTasks\Enums\NdtMethodCode;
use App\Modules\NdtTasks\Models\NdtMethod;
use App\Modules\NdtTasks\Models\NdtTask;
use App\Modules\Objects\Models\City;
use App\Modules\Objects\Models\NdtObject;
use App\Modules\Radiography\Enums\RtStatus;
use App\Modules\Radiography\Models\RtResult;
use App\Modules\Shifts\Enums\ShiftStatus;
use App\Modules\Shifts\Enums\ShiftType;
use App\Modules\Shifts\Models\Shift;
use App\Modules\Welds\Enums\WeldStatus;
use App\Modules\Welds\Models\Weld;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class ShiftsWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_lab_shift_records_inventory_and_can_be_completed(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $labUser = User::query()->create([
            'name' => 'Лаборант',
            'email' => 'lab@example.test',
            'password' => 'password',
            'status' => UserStatus::Active,
        ]);
        $labUser->assignRole(Role::findByName('Лаборант', 'web'));

        $city = City::query()->create([
            'name' => 'Пермь',
            'is_active' => true,
            'comment' => null,
        ]);
        $object = NdtObject::query()->create([
            'city_id' => $city->id,
            'name' => 'Участок смен',
            'code' => 'SHIFT',
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
            'last_name' => 'Смирнова',
            'first_name' => 'Анна',
            'middle_name' => null,
            'phone' => null,
            'email' => null,
            'status' => EmployeeStatus::Active,
            'personnel_number' => '2001',
        ]);
        $employee->users()->sync([$labUser->id]);

        $this->actingAs($labUser)
            ->post(route('admin.shifts.store'), [
                'employee_id' => $employee->id,
                'type' => ShiftType::Lab->value,
                'comment' => 'Первая смена',
            ])
            ->assertRedirect();

        $shift = Shift::query()->where('employee_id', $employee->id)->firstOrFail();
        $this->assertSame(ShiftStatus::Open, $shift->status);

        $this->actingAs($labUser)
            ->post(route('admin.shifts.lab.regulatory-works.store', $shift), [
                'worked_at' => now()->toDateTimeString(),
                'description' => 'Проверка вентиляции',
                'comment' => 'Без замечаний',
            ])
            ->assertRedirect();

        $this->actingAs($labUser)
            ->post(route('admin.shifts.lab.film-transactions.store', $shift), [
                'rt_film_id' => null,
                'quantity' => 2,
                'transacted_at' => now()->toDateTimeString(),
                'comment' => 'Прием пленки',
            ])
            ->assertRedirect();

        $this->actingAs($labUser)
            ->post(route('admin.shifts.lab.chemical-transactions.store', $shift), [
                'chemical_type_id' => null,
                'quantity' => 1,
                'transacted_at' => now()->toDateTimeString(),
                'comment' => 'Поступление химии',
            ])
            ->assertRedirect();

        $this->actingAs($labUser)
            ->post(route('admin.shifts.lab.chemical-requests.store', $shift), [
                'chemical_type_id' => null,
                'quantity' => 1,
                'requested_at' => now()->toDateTimeString(),
                'comment' => 'Нужна химия',
            ])
            ->assertRedirect();

        $this->actingAs($labUser)
            ->post(route('admin.shifts.lab.reports.store', $shift), [
                'summary' => 'Смена прошла штатно',
                'comment' => 'Отчет',
                'completed_at' => now()->toDateTimeString(),
            ])
            ->assertRedirect();

        $this->actingAs($labUser)
            ->patch(route('admin.shifts.complete', $shift), [
                'comment' => 'Завершение смены',
            ])
            ->assertRedirect();

        $shift->refresh();
        $this->assertSame(ShiftStatus::Completed, $shift->status);
        $this->assertDatabaseHas('film_inventory_transactions', [
            'shift_id' => $shift->id,
            'operation' => 'received',
            'quantity' => 2,
        ]);
        $this->assertDatabaseHas('chemical_inventory_transactions', [
            'shift_id' => $shift->id,
            'operation' => 'received',
            'quantity' => 1,
        ]);
        $this->assertDatabaseHas('chemical_requests', [
            'shift_id' => $shift->id,
            'quantity' => 1,
        ]);
    }

    public function test_employee_cannot_open_two_decoder_shifts_at_once(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $engineer = User::query()->create([
            'name' => 'Дешифровщик',
            'email' => 'decoder@example.test',
            'password' => 'password',
            'status' => UserStatus::Active,
        ]);
        $engineer->assignRole(Role::findByName('Инженер НК / Дешифровщик', 'web'));

        $city = City::query()->create([
            'name' => 'Самара',
            'is_active' => true,
            'comment' => null,
        ]);
        $object = NdtObject::query()->create([
            'city_id' => $city->id,
            'name' => 'Участок дешифровки',
            'code' => 'DEC',
            'is_active' => true,
            'comment' => null,
        ]);
        $position = Position::query()->create([
            'name' => 'Инженер НК / Дешифровщик',
            'is_active' => true,
            'comment' => null,
        ]);
        $employee = Employee::query()->create([
            'object_id' => $object->id,
            'position_id' => $position->id,
            'last_name' => 'Иванов',
            'first_name' => 'Иван',
            'middle_name' => null,
            'phone' => null,
            'email' => null,
            'status' => EmployeeStatus::Active,
            'personnel_number' => '2002',
        ]);
        $employee->users()->sync([$engineer->id]);

        $this->actingAs($engineer)
            ->post(route('admin.shifts.store'), [
                'employee_id' => $employee->id,
                'type' => ShiftType::Decoder->value,
                'comment' => 'Смена дешифровщика',
            ])
            ->assertRedirect();

        $this->actingAs($engineer)
            ->post(route('admin.shifts.store'), [
                'employee_id' => $employee->id,
                'type' => ShiftType::Decoder->value,
                'comment' => 'Вторая попытка',
            ])
            ->assertSessionHasErrors('employee_id');
    }

    public function test_decoder_shift_records_decryption_and_completion(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $engineer = User::query()->create([
            'name' => 'Дешифровщик',
            'email' => 'decoder-2@example.test',
            'password' => 'password',
            'status' => UserStatus::Active,
        ]);
        $engineer->assignRole(Role::findByName('Инженер НК / Дешифровщик', 'web'));

        $city = City::query()->create([
            'name' => 'Омск',
            'is_active' => true,
            'comment' => null,
        ]);
        $object = NdtObject::query()->create([
            'city_id' => $city->id,
            'name' => 'Участок декодирования',
            'code' => 'DEC2',
            'is_active' => true,
            'comment' => null,
        ]);
        $position = Position::query()->create([
            'name' => 'Инженер НК / Дешифровщик',
            'is_active' => true,
            'comment' => null,
        ]);
        $employee = Employee::query()->create([
            'object_id' => $object->id,
            'position_id' => $position->id,
            'last_name' => 'Сидоров',
            'first_name' => 'Сергей',
            'middle_name' => null,
            'phone' => null,
            'email' => null,
            'status' => EmployeeStatus::Active,
            'personnel_number' => '2003',
        ]);
        $employee->users()->sync([$engineer->id]);

        $method = NdtMethod::query()->where('code', NdtMethodCode::RK->value)->firstOrFail();
        $weld = Weld::query()->create([
            'object_id' => $object->id,
            'title_id' => null,
            'drawing_id' => null,
            'line_id' => null,
            'weld_number' => 'W-DEC-1',
            'diameter' => null,
            'thickness' => null,
            'material_1_id' => null,
            'material_2_id' => null,
            'welded_at' => null,
            'welding_process_id' => null,
            'weld_type_id' => null,
            'pipeline_category_id' => null,
            'medium_id' => null,
            'pwht' => false,
            'normative_document_id' => null,
            'status' => WeldStatus::Created,
        ]);
        $task = NdtTask::query()->create([
            'task_number' => 'TASK-DEC-1',
            'ndt_request_id' => null,
            'object_id' => $object->id,
            'ndt_method_id' => $method->id,
            'assignee_employee_id' => $employee->id,
            'planned_date' => now()->toDateString(),
            'priority' => null,
            'comment' => null,
            'status' => 'created',
        ]);
        $ndtResult = NdtResult::query()->create([
            'ndt_task_id' => $task->id,
            'weld_id' => $weld->id,
            'ndt_method_id' => $method->id,
            'executor_employee_id' => $employee->id,
            'equipment_id' => null,
            'normative_document_id' => null,
            'control_date' => now()->toDateString(),
            'result_text' => 'Материал для смены',
            'comment' => null,
            'status' => NdtResultStatus::Created,
        ]);
        $rtResult = RtResult::query()->create([
            'ndt_result_id' => $ndtResult->id,
            'film_type_id' => null,
            'barcode' => 'RT-DEC-1',
            'conclusion_number' => null,
            'control_date' => now()->toDateString(),
            'conclusion_date' => null,
            'archive_location' => null,
            'result_text' => null,
            'comment' => null,
            'reshoot_reason' => null,
            'status' => RtStatus::Assigned,
            'decoded_at' => null,
            'sent_to_analysis_at' => null,
            'included_in_conclusion_at' => null,
            'archived_at' => null,
        ]);

        $this->actingAs($engineer)
            ->post(route('admin.shifts.store'), [
                'employee_id' => $employee->id,
                'type' => ShiftType::Decoder->value,
                'comment' => 'Смена дешифровщика',
            ])
            ->assertRedirect();

        $shift = Shift::query()->where('employee_id', $employee->id)->where('type', ShiftType::Decoder)->firstOrFail();

        $this->actingAs($engineer)
            ->post(route('admin.shifts.decoder.film-groups.store', $shift), [
                'rt_result_id' => $rtResult->id,
                'group_name' => 'Группа 1',
                'viewed_at' => now()->toDateTimeString(),
                'comment' => 'Просмотрено',
            ])
            ->assertRedirect();

        $this->actingAs($engineer)
            ->post(route('admin.shifts.decoder.cleanups.store', $shift), [
                'completed_at' => now()->toDateTimeString(),
                'comment' => 'Рабочее место очищено',
            ])
            ->assertRedirect();

        $this->actingAs($engineer)
            ->post(route('admin.shifts.decoder.decryptions.store', $shift), [
                'rt_result_id' => $rtResult->id,
                'result_text' => 'Дефектов не обнаружено',
                'analysis_comment' => 'Комментарий',
                'decrypted_at' => now()->toDateTimeString(),
            ])
            ->assertRedirect();

        $this->actingAs($engineer)
            ->post(route('admin.shifts.decoder.reports.store', $shift), [
                'summary' => 'Смена завершена',
                'comment' => 'Отчет',
                'completed_at' => now()->toDateTimeString(),
            ])
            ->assertRedirect();

        $this->actingAs($engineer)
            ->patch(route('admin.shifts.complete', $shift), [
                'comment' => 'Завершение смены',
            ])
            ->assertRedirect();

        $shift->refresh();
        $rtResult->refresh();

        $this->assertSame(ShiftStatus::Completed, $shift->status);
        $this->assertSame(RtStatus::Decoded, $rtResult->status);
        $this->assertDatabaseHas('decoder_film_groups', [
            'shift_id' => $shift->id,
            'group_name' => 'Группа 1',
        ]);
        $this->assertDatabaseHas('decoder_cleanups', [
            'shift_id' => $shift->id,
        ]);
        $this->assertDatabaseHas('decoder_decryptions', [
            'shift_id' => $shift->id,
            'rt_result_id' => $rtResult->id,
        ]);
    }
}
