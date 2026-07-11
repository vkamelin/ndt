<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Models\User;
use App\Modules\Admin\Models\Drawing;
use App\Modules\Admin\Models\Line;
use App\Modules\Admin\Models\Material;
use App\Modules\Admin\Models\Medium;
use App\Modules\Admin\Models\NormativeDocument;
use App\Modules\Admin\Models\PipelineCategory;
use App\Modules\Admin\Models\Title;
use App\Modules\Admin\Models\WeldingProcess;
use App\Modules\Admin\Models\WeldType;
use App\Modules\Auth\Enums\UserStatus;
use App\Modules\Employees\Enums\EmployeeStatus;
use App\Modules\Employees\Models\Employee;
use App\Modules\Employees\Models\Position;
use App\Modules\NdtRequests\Enums\NdtRequestStatus;
use App\Modules\NdtRequests\Models\NdtRequest;
use App\Modules\NdtTasks\DTO\AssignNdtTaskData;
use App\Modules\NdtTasks\Models\NdtMethod;
use App\Modules\NdtTasks\Services\NdtTaskService;
use App\Modules\Notifications\Enums\NotificationType;
use App\Modules\Notifications\Services\NotificationService;
use App\Modules\Objects\Models\City;
use App\Modules\Objects\Models\NdtObject;
use App\Modules\Welds\Enums\WeldStatus;
use App\Modules\Welds\Models\Weld;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class DashboardOverviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_notifications_and_worklist_blocks(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::query()->where('email', 'admin@example.test')->firstOrFail();
        $this->createWorkbenchData($admin);

        app(NotificationService::class)->notifyUser(
            $admin,
            NotificationType::QueueFailure,
            'queue_failure',
            ['message' => 'Проверка панели уведомлений.'],
        );

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText('Рабочий стол')
            ->assertSeeText('На утверждении')
            ->assertSeeText('Просроченные')
            ->assertSeeText('Проверка панели уведомлений.');
    }

    private function createWorkbenchData(User $admin): void
    {
        $city = City::query()->create([
            'name' => 'Уфа',
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

        NdtRequest::query()->create([
            'request_number' => 'REQ-3',
            'request_date' => now()->toDateString(),
            'organization_id' => null,
            'object_id' => $object->id,
            'title_id' => null,
            'priority' => null,
            'due_date' => null,
            'basis' => null,
            'comment' => null,
            'status' => NdtRequestStatus::Approval,
        ]);

        $position = Position::query()->create([
            'name' => 'Дефектоскопист',
            'is_active' => true,
            'comment' => null,
        ]);
        $executorUser = User::query()->create([
            'name' => 'Исполнитель',
            'email' => 'dashboard-executor@example.test',
            'password' => 'password',
            'status' => UserStatus::Active,
        ]);
        $executorUser->assignRole(Role::findByName('Дефектоскопист', 'web'));
        $employee = Employee::query()->create([
            'object_id' => $object->id,
            'position_id' => $position->id,
            'last_name' => 'Сидоров',
            'first_name' => 'Сидор',
            'middle_name' => null,
            'phone' => null,
            'email' => null,
            'status' => EmployeeStatus::Active,
            'personnel_number' => '202',
        ]);
        $employee->users()->attach($executorUser->id);

        $method = NdtMethod::query()->where('code', 'rk')->firstOrFail();

        $title = Title::query()->create([
            'name' => 'Титул 3',
            'is_active' => true,
            'comment' => null,
        ]);
        $drawing = Drawing::query()->create([
            'name' => 'Чертеж 3',
            'is_active' => true,
            'comment' => null,
        ]);
        $line = Line::query()->create([
            'name' => 'Линия 3',
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
            'name' => 'Категория 3',
            'is_active' => true,
            'comment' => null,
        ]);
        $medium = Medium::query()->create([
            'name' => 'Газ',
            'is_active' => true,
            'comment' => null,
        ]);
        $normativeDocument = NormativeDocument::query()->create([
            'name' => 'НД 3',
            'is_active' => true,
            'comment' => null,
        ]);

        $weld = Weld::query()->create([
            'object_id' => $object->id,
            'title_id' => $title->id,
            'drawing_id' => $drawing->id,
            'line_id' => $line->id,
            'weld_number' => '3',
            'diameter' => 120,
            'thickness' => 8,
            'material_1_id' => $material->id,
            'material_2_id' => null,
            'welded_at' => now()->subDay()->toDateString(),
            'welding_process_id' => $process->id,
            'weld_type_id' => $weldType->id,
            'pipeline_category_id' => $category->id,
            'medium_id' => $medium->id,
            'pwht' => null,
            'normative_document_id' => $normativeDocument->id,
            'status' => WeldStatus::Good,
        ]);
        $weld->ndtMethods()->sync([$method->id]);

        $request = NdtRequest::query()->create([
            'request_number' => 'REQ-4',
            'request_date' => now()->toDateString(),
            'organization_id' => null,
            'object_id' => $object->id,
            'title_id' => null,
            'priority' => null,
            'due_date' => null,
            'basis' => null,
            'comment' => null,
            'status' => NdtRequestStatus::Registered,
        ]);

        app(NdtTaskService::class)->create(
            new AssignNdtTaskData(
                taskNumber: 'TASK-4',
                ndtRequestId: $request->id,
                objectId: $object->id,
                ndtMethodId: $method->id,
                assigneeEmployeeId: $employee->id,
                plannedDate: now()->subDay()->toDateString(),
                priority: null,
                comment: null,
                weldIds: [$weld->id],
            ),
            $admin,
        );
    }
}
