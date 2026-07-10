<?php

declare(strict_types=1);

namespace Tests\Feature\Equipment;

use App\Models\User;
use App\Modules\Auth\Enums\UserStatus;
use App\Modules\Employees\Enums\EmployeeStatus;
use App\Modules\Employees\Enums\QualificationMethod;
use App\Modules\Employees\Models\Employee;
use App\Modules\Employees\Models\EmployeeQualification;
use App\Modules\Employees\Models\Position;
use App\Modules\Equipment\Enums\EquipmentStatus;
use App\Modules\Equipment\Models\Equipment;
use App\Modules\Equipment\Models\EquipmentCalibration;
use App\Modules\Equipment\Models\EquipmentType;
use App\Modules\Equipment\Models\EquipmentVerification;
use App\Modules\Admin\Models\Drawing;
use App\Modules\Admin\Models\Line;
use App\Modules\Admin\Models\Material;
use App\Modules\Admin\Models\Medium;
use App\Modules\Admin\Models\NormativeDocument;
use App\Modules\Admin\Models\PipelineCategory;
use App\Modules\Admin\Models\Title;
use App\Modules\Admin\Models\WeldType;
use App\Modules\Admin\Models\WeldingProcess;
use App\Modules\NdtRequests\Enums\NdtRequestStatus;
use App\Modules\NdtRequests\Models\NdtRequest;
use App\Modules\NdtResults\DTO\NdtResultData;
use App\Modules\NdtResults\Services\NdtResultService;
use App\Modules\NdtTasks\DTO\AssignNdtTaskData;
use App\Modules\NdtTasks\Models\NdtMethod;
use App\Modules\NdtTasks\Services\NdtTaskService;
use App\Modules\Objects\Models\City;
use App\Modules\Objects\Models\NdtObject;
use App\Modules\Welds\Enums\WeldStatus;
use App\Modules\Welds\Models\Weld;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class EquipmentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_equipment_and_dashboard_shows_expiring_checks(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $admin = User::query()->where('email', 'admin@example.test')->firstOrFail();
        $city = City::query()->create([
            'name' => 'Челябинск',
            'is_active' => true,
            'comment' => null,
        ]);
        $object = NdtObject::query()->create([
            'city_id' => $city->id,
            'name' => 'Участок 1',
            'code' => null,
            'is_active' => true,
            'comment' => null,
        ]);
        $type = EquipmentType::query()->create([
            'code' => 'ultrasonic',
            'name' => 'Ультразвуковой дефектоскоп',
            'is_active' => true,
            'comment' => null,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.equipment.store'), [
                'equipment_type_id' => $type->id,
                'object_id' => $object->id,
                'name' => 'Дефектоскоп УД2-70',
                'inventory_number' => 'INV-100',
                'serial_number' => 'SN-100',
                'manufacturer' => 'Завод',
                'model' => 'UD2-70',
                'status' => EquipmentStatus::Available->value,
                'purchased_at' => '2026-01-10',
                'comment' => 'Пробная карточка',
            ])
            ->assertRedirect();

        $equipment = Equipment::query()->where('inventory_number', 'INV-100')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.equipment.verifications.store', $equipment), [
                'verified_at' => now()->subMonth()->toDateString(),
                'valid_until' => now()->addDays(10)->toDateString(),
                'certificate_number' => 'VER-1',
                'comment' => 'Поверка',
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->post(route('admin.equipment.calibrations.store', $equipment), [
                'calibrated_at' => now()->subMonth()->toDateString(),
                'valid_until' => now()->addDays(10)->toDateString(),
                'certificate_number' => 'CAL-1',
                'comment' => 'Калибровка',
            ])
            ->assertRedirect();

        $employee = Employee::query()->create([
            'object_id' => $object->id,
            'position_id' => Position::query()->create([
                'name' => 'Дефектоскопист',
                'is_active' => true,
                'comment' => null,
            ])->id,
            'last_name' => 'Иванов',
            'first_name' => 'Иван',
            'middle_name' => null,
            'phone' => null,
            'email' => null,
            'status' => EmployeeStatus::Active,
            'personnel_number' => '200',
        ]);
        EmployeeQualification::query()->create([
            'employee_id' => $employee->id,
            'method' => QualificationMethod::RK,
            'valid_until' => now()->addDays(10)->toDateString(),
            'comment' => 'Квалификация',
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText('Дефектоскоп УД2-70')
            ->assertSeeText('Иванов');

        $this->assertDatabaseHas('equipment_verifications', [
            'equipment_id' => $equipment->id,
            'certificate_number' => 'VER-1',
        ]);
        $this->assertDatabaseHas('equipment_calibrations', [
            'equipment_id' => $equipment->id,
            'certificate_number' => 'CAL-1',
        ]);
    }

    public function test_strict_guard_blocks_unqualified_executor_in_task_service(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        config(['equipment.strict_qualification_guard' => true]);

        $admin = User::query()->where('email', 'admin@example.test')->firstOrFail();
        $city = City::query()->create([
            'name' => 'Пермь',
            'is_active' => true,
            'comment' => null,
        ]);
        $object = NdtObject::query()->create([
            'city_id' => $city->id,
            'name' => 'Участок 2',
            'code' => null,
            'is_active' => true,
            'comment' => null,
        ]);
        $request = NdtRequest::query()->create([
            'request_number' => 'REQ-1',
            'request_date' => now()->toDateString(),
            'organization_id' => null,
            'object_id' => $object->id,
            'title_id' => null,
            'priority' => null,
            'due_date' => null,
            'basis' => null,
            'comment' => null,
            'status' => NdtRequestStatus::Draft,
        ]);
        $method = NdtMethod::query()->where('code', 'rk')->firstOrFail();
        $title = Title::query()->create([
            'name' => 'Титул 1',
            'is_active' => true,
            'comment' => null,
        ]);
        $drawing = Drawing::query()->create([
            'name' => 'Чертеж 1',
            'is_active' => true,
            'comment' => null,
        ]);
        $line = Line::query()->create([
            'name' => 'Линия 1',
            'is_active' => true,
            'comment' => null,
        ]);
        $material = Material::query()->create([
            'name' => 'Сталь 20',
            'is_active' => true,
            'comment' => null,
        ]);
        $process = WeldingProcess::query()->create([
            'name' => 'РД',
            'is_active' => true,
            'comment' => null,
        ]);
        $weldType = WeldType::query()->create([
            'name' => 'Стыковой',
            'is_active' => true,
            'comment' => null,
        ]);
        $category = PipelineCategory::query()->create([
            'name' => 'Категория 1',
            'is_active' => true,
            'comment' => null,
        ]);
        $medium = Medium::query()->create([
            'name' => 'Газ',
            'is_active' => true,
            'comment' => null,
        ]);
        $normativeDocument = NormativeDocument::query()->create([
            'name' => 'НД 1',
            'is_active' => true,
            'comment' => null,
        ]);
        $weld = Weld::query()->create([
            'object_id' => $object->id,
            'title_id' => $title->id,
            'drawing_id' => $drawing->id,
            'line_id' => $line->id,
            'weld_number' => 'W-1',
            'diameter' => null,
            'thickness' => null,
            'material_1_id' => $material->id,
            'material_2_id' => $material->id,
            'welded_at' => null,
            'welding_process_id' => $process->id,
            'weld_type_id' => $weldType->id,
            'pipeline_category_id' => $category->id,
            'medium_id' => $medium->id,
            'pwht' => false,
            'normative_document_id' => $normativeDocument->id,
            'status' => WeldStatus::Created,
        ]);
        $weld->ndtMethods()->sync([$method->id]);
        $request->welds()->sync([$weld->id]);

        $position = Position::query()->create([
            'name' => 'Дефектоскопист',
            'is_active' => true,
            'comment' => null,
        ]);
        $unqualifiedUser = User::factory()->create([
            'name' => 'Без квалификации',
            'email' => 'unqualified@example.test',
            'password' => 'password',
            'status' => UserStatus::Active,
        ]);
        $unqualifiedUser->assignRole(Role::findByName('Дефектоскопист', 'web'));
        $unqualifiedEmployee = Employee::query()->create([
            'object_id' => $object->id,
            'position_id' => $position->id,
            'last_name' => 'Петров',
            'first_name' => 'Петр',
            'middle_name' => null,
            'phone' => null,
            'email' => null,
            'status' => EmployeeStatus::Active,
            'personnel_number' => '201',
        ]);
        $unqualifiedEmployee->users()->sync([$unqualifiedUser->id]);

        $taskService = app(NdtTaskService::class);

        $this->expectException(ValidationException::class);
        $taskService->create(
            AssignNdtTaskData::fromArray([
                'task_number' => 'TASK-1',
                'ndt_request_id' => $request->id,
                'object_id' => $object->id,
                'ndt_method_id' => $method->id,
                'assignee_employee_id' => $unqualifiedEmployee->id,
                'planned_date' => now()->toDateString(),
                'priority' => null,
                'comment' => null,
                'weld_ids' => [$weld->id],
            ]),
            actor: $admin,
        );
    }

    public function test_strict_guard_blocks_result_when_equipment_verification_is_expired(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        config(['equipment.strict_qualification_guard' => true]);

        $admin = User::query()->where('email', 'admin@example.test')->firstOrFail();
        $city = City::query()->create([
            'name' => 'Екатеринбург',
            'is_active' => true,
            'comment' => null,
        ]);
        $object = NdtObject::query()->create([
            'city_id' => $city->id,
            'name' => 'Участок 3',
            'code' => null,
            'is_active' => true,
            'comment' => null,
        ]);
        $request = NdtRequest::query()->create([
            'request_number' => 'REQ-2',
            'request_date' => now()->toDateString(),
            'organization_id' => null,
            'object_id' => $object->id,
            'title_id' => null,
            'priority' => null,
            'due_date' => null,
            'basis' => null,
            'comment' => null,
            'status' => NdtRequestStatus::Draft,
        ]);
        $method = NdtMethod::query()->where('code', 'rk')->firstOrFail();
        $title = Title::query()->create([
            'name' => 'Титул 2',
            'is_active' => true,
            'comment' => null,
        ]);
        $drawing = Drawing::query()->create([
            'name' => 'Чертеж 2',
            'is_active' => true,
            'comment' => null,
        ]);
        $line = Line::query()->create([
            'name' => 'Линия 2',
            'is_active' => true,
            'comment' => null,
        ]);
        $material = Material::query()->create([
            'name' => 'Сталь 09Г2С',
            'is_active' => true,
            'comment' => null,
        ]);
        $process = WeldingProcess::query()->create([
            'name' => 'ММА',
            'is_active' => true,
            'comment' => null,
        ]);
        $weldType = WeldType::query()->create([
            'name' => 'Угловой',
            'is_active' => true,
            'comment' => null,
        ]);
        $category = PipelineCategory::query()->create([
            'name' => 'Категория 2',
            'is_active' => true,
            'comment' => null,
        ]);
        $medium = Medium::query()->create([
            'name' => 'Вода',
            'is_active' => true,
            'comment' => null,
        ]);
        $normativeDocument = NormativeDocument::query()->create([
            'name' => 'НД 2',
            'is_active' => true,
            'comment' => null,
        ]);
        $weld = Weld::query()->create([
            'object_id' => $object->id,
            'title_id' => $title->id,
            'drawing_id' => $drawing->id,
            'line_id' => $line->id,
            'weld_number' => 'W-2',
            'diameter' => null,
            'thickness' => null,
            'material_1_id' => $material->id,
            'material_2_id' => $material->id,
            'welded_at' => null,
            'welding_process_id' => $process->id,
            'weld_type_id' => $weldType->id,
            'pipeline_category_id' => $category->id,
            'medium_id' => $medium->id,
            'pwht' => false,
            'normative_document_id' => $normativeDocument->id,
            'status' => WeldStatus::Created,
        ]);
        $weld->ndtMethods()->sync([$method->id]);
        $request->welds()->sync([$weld->id]);

        $position = Position::query()->create([
            'name' => 'Инженер НК',
            'is_active' => true,
            'comment' => null,
        ]);
        $user = User::factory()->create([
            'name' => 'Квалифицированный',
            'email' => 'qualified@example.test',
            'password' => 'password',
            'status' => UserStatus::Active,
        ]);
        $user->assignRole(Role::findByName('Инженер НК / Дешифровщик', 'web'));
        $employee = Employee::query()->create([
            'object_id' => $object->id,
            'position_id' => $position->id,
            'last_name' => 'Сидоров',
            'first_name' => 'Сергей',
            'middle_name' => null,
            'phone' => null,
            'email' => null,
            'status' => EmployeeStatus::Active,
            'personnel_number' => '202',
        ]);
        $employee->users()->sync([$user->id]);
        EmployeeQualification::query()->create([
            'employee_id' => $employee->id,
            'method' => QualificationMethod::RK,
            'valid_until' => now()->addMonth()->toDateString(),
            'comment' => null,
        ]);

        $task = app(NdtTaskService::class)->create(
            AssignNdtTaskData::fromArray([
                'task_number' => 'TASK-2',
                'ndt_request_id' => $request->id,
                'object_id' => $object->id,
                'ndt_method_id' => $method->id,
                'assignee_employee_id' => $employee->id,
                'planned_date' => now()->toDateString(),
                'priority' => null,
                'comment' => null,
                'weld_ids' => [$weld->id],
            ]),
            actor: $admin,
        );

        $equipmentType = EquipmentType::query()->create([
            'code' => 'ut',
            'name' => 'Ультразвуковой дефектоскоп',
            'is_active' => true,
            'comment' => null,
        ]);
        $equipment = Equipment::query()->create([
            'equipment_type_id' => $equipmentType->id,
            'object_id' => $object->id,
            'name' => 'Дефектоскоп',
            'inventory_number' => 'EQ-1',
            'serial_number' => 'SER-1',
            'manufacturer' => null,
            'model' => null,
            'status' => EquipmentStatus::Available,
            'purchased_at' => null,
            'write_off_at' => null,
            'comment' => null,
        ]);
        EquipmentVerification::query()->create([
            'equipment_id' => $equipment->id,
            'recorded_by_user_id' => $admin->id,
            'verified_at' => now()->subYear()->toDateString(),
            'valid_until' => now()->subDay()->toDateString(),
            'certificate_number' => 'OLD-VER',
            'comment' => null,
        ]);
        EquipmentCalibration::query()->create([
            'equipment_id' => $equipment->id,
            'recorded_by_user_id' => $admin->id,
            'calibrated_at' => now()->subYear()->toDateString(),
            'valid_until' => now()->subDay()->toDateString(),
            'certificate_number' => 'OLD-CAL',
            'comment' => null,
        ]);

        $this->expectException(ValidationException::class);

        app(NdtResultService::class)->create(
            NdtResultData::fromArray([
                'ndt_task_id' => $task->id,
                'weld_id' => $weld->id,
                'ndt_method_id' => $method->id,
                'executor_employee_id' => $employee->id,
                'equipment_id' => $equipment->id,
                'normative_document_id' => null,
                'control_date' => now()->toDateString(),
                'result_text' => 'OK',
                'comment' => null,
            ]),
            actor: $admin,
        );
    }
}
