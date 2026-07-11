<?php

declare(strict_types=1);

namespace Tests\Feature\NdtResults;

use App\Models\User;
use App\Modules\Admin\Models\DefectType;
use App\Modules\Auth\Enums\UserStatus;
use App\Modules\Employees\Enums\EmployeeStatus;
use App\Modules\Employees\Models\Employee;
use App\Modules\Employees\Models\Position;
use App\Modules\NdtResults\Enums\NdtResultStatus;
use App\Modules\NdtResults\Models\NdtResult;
use App\Modules\NdtTasks\Enums\NdtMethodCode;
use App\Modules\NdtTasks\Enums\NdtTaskStatus;
use App\Modules\NdtTasks\Models\NdtMethod;
use App\Modules\NdtTasks\Models\NdtTask;
use App\Modules\Objects\Models\City;
use App\Modules\Objects\Models\NdtObject;
use App\Modules\Welds\Enums\WeldStatus;
use App\Modules\Welds\Models\Weld;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class NdtResultsWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_defectoscopist_can_create_result_and_send_it_to_analysis(): void
    {
        $this->seed(DatabaseSeeder::class);

        $defectoscopist = User::query()->create([
            'name' => 'Дефектоскопист',
            'email' => 'defectoscopist@example.test',
            'password' => 'password',
            'status' => UserStatus::Active,
        ]);
        $defectoscopist->assignRole(Role::findByName('Дефектоскопист', 'web'));

        $city = City::query()->create([
            'name' => 'Екатеринбург',
            'is_active' => true,
            'comment' => null,
        ]);
        $object = NdtObject::query()->create([
            'city_id' => $city->id,
            'name' => 'Участок Результатов',
            'code' => 'UR',
            'is_active' => true,
            'comment' => null,
        ]);
        $position = Position::query()->create([
            'name' => 'Дефектоскопист',
            'is_active' => true,
            'comment' => null,
        ]);
        $executor = Employee::query()->create([
            'object_id' => $object->id,
            'position_id' => $position->id,
            'last_name' => 'Иванов',
            'first_name' => 'Иван',
            'middle_name' => null,
            'phone' => null,
            'email' => null,
            'status' => EmployeeStatus::Active,
            'personnel_number' => '500',
        ]);
        $executor->users()->sync([$defectoscopist->id]);

        $method = NdtMethod::query()->where('code', NdtMethodCode::VIK->value)->firstOrFail();
        $weld = $this->createWeld($object->id, 'W-500');
        $task = $this->createTask($object->id, $method->id, $executor->id, $weld->id, 'NT-500');

        $this->actingAs($defectoscopist)
            ->post(route('admin.ndt-results.store'), [
                'ndt_task_id' => $task->id,
                'weld_id' => $weld->id,
                'executor_employee_id' => $executor->id,
                'control_date' => '2026-07-10',
                'result_text' => 'Контроль выполнен',
                'comment' => 'Первичный результат',
            ])
            ->assertRedirect();

        $result = NdtResult::query()->where('ndt_task_id', $task->id)->firstOrFail();

        $this->assertSame(NdtResultStatus::Created, $result->status);
        $this->assertDatabaseHas('audit_logs', [
            'subject_type' => NdtResult::class,
            'subject_id' => $result->id,
            'event' => 'ndt_result.created',
        ]);

        $this->actingAs($defectoscopist)
            ->patch(route('admin.ndt-results.status.analysis', $result), [
                'comment' => 'Передан на анализ',
            ])
            ->assertRedirect();

        $result->refresh();
        $weld->refresh();

        $this->assertSame(NdtResultStatus::InAnalysis, $result->status);
        $this->assertSame(WeldStatus::WaitingAnalysis, $weld->status);
        $this->assertDatabaseHas('ndt_result_status_history', [
            'ndt_result_id' => $result->id,
            'to_status' => NdtResultStatus::InAnalysis->value,
        ]);
    }

    public function test_engineer_can_mark_defect_and_it_updates_weld_status(): void
    {
        $this->seed(DatabaseSeeder::class);

        $engineer = User::query()->create([
            'name' => 'Инженер',
            'email' => 'engineer@example.test',
            'password' => 'password',
            'status' => UserStatus::Active,
        ]);
        $engineer->assignRole(Role::findByName('Инженер НК / Дешифровщик', 'web'));

        $defectoscopist = User::query()->create([
            'name' => 'Дефектоскопист',
            'email' => 'defect-mark@example.test',
            'password' => 'password',
            'status' => UserStatus::Active,
        ]);
        $defectoscopist->assignRole(Role::findByName('Дефектоскопист', 'web'));

        $city = City::query()->create([
            'name' => 'Пермь',
            'is_active' => true,
            'comment' => null,
        ]);
        $object = NdtObject::query()->create([
            'city_id' => $city->id,
            'name' => 'Участок Дефектов',
            'code' => 'UD',
            'is_active' => true,
            'comment' => null,
        ]);
        $position = Position::query()->create([
            'name' => 'Дефектоскопист',
            'is_active' => true,
            'comment' => null,
        ]);
        $executor = Employee::query()->create([
            'object_id' => $object->id,
            'position_id' => $position->id,
            'last_name' => 'Петров',
            'first_name' => 'Петр',
            'middle_name' => null,
            'phone' => null,
            'email' => null,
            'status' => EmployeeStatus::Active,
            'personnel_number' => '501',
        ]);
        $executor->users()->sync([$defectoscopist->id]);
        Employee::query()->create([
            'object_id' => $object->id,
            'position_id' => $position->id,
            'last_name' => 'Ильин',
            'first_name' => 'Илья',
            'middle_name' => null,
            'phone' => null,
            'email' => null,
            'status' => EmployeeStatus::Active,
            'personnel_number' => '511',
        ])->users()->sync([$engineer->id]);

        $method = NdtMethod::query()->where('code', NdtMethodCode::VIK->value)->firstOrFail();
        $weld = $this->createWeld($object->id, 'W-501');
        $task = $this->createTask($object->id, $method->id, $executor->id, $weld->id, 'NT-501');
        $result = $this->createResult($defectoscopist, $task, $weld, $executor->id);

        $defectType = DefectType::query()->create([
            'name' => 'Подрез',
            'is_active' => true,
            'comment' => null,
        ]);

        $this->actingAs($engineer)
            ->post(route('admin.ndt-results.defects.store', $result), [
                'defect_type_id' => $defectType->id,
                'description' => 'Выявлен подрез по кромке',
                'comment' => 'Требуется анализ',
            ])
            ->assertRedirect();

        $result->refresh();
        $weld->refresh();

        $this->assertSame(NdtResultStatus::Defect, $result->status);
        $this->assertSame(WeldStatus::Defect, $weld->status);
        $this->assertDatabaseHas('ndt_result_defects', [
            'ndt_result_id' => $result->id,
            'defect_type_id' => $defectType->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'subject_type' => NdtResult::class,
            'subject_id' => $result->id,
            'event' => 'ndt_result.defect_added',
        ]);
    }

    public function test_method_specific_forms_are_persisted(): void
    {
        $this->seed(DatabaseSeeder::class);

        $engineer = User::query()->create([
            'name' => 'Инженер',
            'email' => 'vik-form@example.test',
            'password' => 'password',
            'status' => UserStatus::Active,
        ]);
        $engineer->assignRole(Role::findByName('Инженер НК / Дешифровщик', 'web'));

        $defectoscopist = User::query()->create([
            'name' => 'Дефектоскопист',
            'email' => 'vik-form-defect@example.test',
            'password' => 'password',
            'status' => UserStatus::Active,
        ]);
        $defectoscopist->assignRole(Role::findByName('Дефектоскопист', 'web'));

        $city = City::query()->create([
            'name' => 'Самара',
            'is_active' => true,
            'comment' => null,
        ]);
        $object = NdtObject::query()->create([
            'city_id' => $city->id,
            'name' => 'Участок Форм',
            'code' => 'UF',
            'is_active' => true,
            'comment' => null,
        ]);
        $position = Position::query()->create([
            'name' => 'Дефектоскопист',
            'is_active' => true,
            'comment' => null,
        ]);
        $executor = Employee::query()->create([
            'object_id' => $object->id,
            'position_id' => $position->id,
            'last_name' => 'Смирнов',
            'first_name' => 'Семен',
            'middle_name' => null,
            'phone' => null,
            'email' => null,
            'status' => EmployeeStatus::Active,
            'personnel_number' => '502',
        ]);
        $executor->users()->sync([$defectoscopist->id]);
        Employee::query()->create([
            'object_id' => $object->id,
            'position_id' => $position->id,
            'last_name' => 'Сергеев',
            'first_name' => 'Сергей',
            'middle_name' => null,
            'phone' => null,
            'email' => null,
            'status' => EmployeeStatus::Active,
            'personnel_number' => '512',
        ])->users()->sync([$engineer->id]);

        $this->actingAs($engineer);

        foreach ([
            NdtMethodCode::VIK->value => [
                'route' => 'admin.ndt-results.vt.update',
                'table' => 'vt_results',
                'payload' => [
                    'conclusion_number' => 'VIK-01',
                    'conclusion_date' => '2026-07-10',
                    'measurements' => 'Параметр измерений',
                    'transfer_register_number' => 'TR-VIK-1',
                    'act_number' => 'ACT-VIK-1',
                ],
            ],
            NdtMethodCode::PVK->value => [
                'route' => 'admin.ndt-results.pt.update',
                'table' => 'pt_results',
                'payload' => [
                    'conclusion_number' => 'PVK-01',
                    'conclusion_date' => '2026-07-10',
                    'control_zone' => 'Зона 1',
                    'materials_used' => 'Капиллярный набор',
                    'transfer_register_number' => 'TR-PVK-1',
                    'act_number' => 'ACT-PVK-1',
                ],
            ],
            NdtMethodCode::MK->value => [
                'route' => 'admin.ndt-results.mt.update',
                'table' => 'mt_results',
                'payload' => [
                    'conclusion_number' => 'MK-01',
                    'conclusion_date' => '2026-07-10',
                    'control_zone' => 'Зона 2',
                    'material' => 'Сталь 20',
                    'control_parameters' => 'Поле 1, ток 2',
                    'transfer_register_number' => 'TR-MK-1',
                    'act_number' => 'ACT-MK-1',
                ],
            ],
            NdtMethodCode::UK->value => [
                'route' => 'admin.ndt-results.ut.update',
                'table' => 'ut_results',
                'payload' => [
                    'conclusion_number' => 'UK-01',
                    'conclusion_date' => '2026-07-10',
                    'sounding_scheme' => 'Схема А',
                    'transducer' => 'ПЭП 2.5',
                    'tuning_parameters' => 'Настройка 1',
                    'transfer_register_number' => 'TR-UK-1',
                    'act_number' => 'ACT-UK-1',
                ],
            ],
        ] as $methodCode => $config) {
            $method = NdtMethod::query()->where('code', $methodCode)->firstOrFail();
            $weld = $this->createWeld($object->id, 'W-'.$methodCode);
            $task = $this->createTask($object->id, $method->id, $executor->id, $weld->id, 'NT-'.$methodCode);
            $result = $this->createResult($defectoscopist, $task, $weld, $executor->id);

            $this->actingAs($engineer)
                ->patch(route($config['route'], $result), $config['payload'])
                ->assertRedirect();

            $expected = array_merge(
                ['ndt_result_id' => $result->id],
                $config['payload'],
            );
            $expected['conclusion_date'] = $expected['conclusion_date'].' 00:00:00';

            $this->assertDatabaseHas($config['table'], $expected);
        }
    }

    private function createWeld(int $objectId, string $weldNumber): Weld
    {
        return Weld::query()->create([
            'object_id' => $objectId,
            'title_id' => null,
            'drawing_id' => null,
            'line_id' => null,
            'weld_number' => $weldNumber,
            'diameter' => null,
            'thickness' => null,
            'material_1_id' => null,
            'material_2_id' => null,
            'welded_at' => null,
            'welding_process_id' => null,
            'weld_type_id' => null,
            'pipeline_category_id' => null,
            'medium_id' => null,
            'pwht' => null,
            'normative_document_id' => null,
            'status' => WeldStatus::Created->value,
        ]);
    }

    private function createTask(int $objectId, int $methodId, int $employeeId, int $weldId, string $taskNumber): NdtTask
    {
        $task = NdtTask::query()->create([
            'task_number' => $taskNumber,
            'ndt_request_id' => null,
            'object_id' => $objectId,
            'ndt_method_id' => $methodId,
            'assignee_employee_id' => $employeeId,
            'planned_date' => '2026-07-10',
            'priority' => 'Высокий',
            'comment' => null,
            'status' => NdtTaskStatus::Assigned->value,
        ]);

        $task->welds()->attach($weldId, ['position_number' => 1]);

        return $task;
    }

    private function createResult(User $actor, NdtTask $task, Weld $weld, int $employeeId): NdtResult
    {
        $this->actingAs($actor)
            ->post(route('admin.ndt-results.store'), [
                'ndt_task_id' => $task->id,
                'weld_id' => $weld->id,
                'executor_employee_id' => $employeeId,
                'control_date' => '2026-07-10',
                'result_text' => 'Контроль',
                'comment' => null,
            ])
            ->assertRedirect();

        $result = NdtResult::query()->where('ndt_task_id', $task->id)->firstOrFail();

        $this->actingAs($actor)
            ->patch(route('admin.ndt-results.status.analysis', $result), [
                'comment' => 'Передан на анализ',
            ])
            ->assertRedirect();

        return $result->refresh();
    }
}
