<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

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
use App\Modules\NdtTasks\Models\NdtTask;
use App\Modules\NdtTasks\Services\NdtTaskService;
use App\Modules\Notifications\Enums\NotificationType;
use App\Modules\Notifications\Models\Notification;
use App\Modules\Notifications\Services\NotificationService;
use App\Modules\Objects\Models\City;
use App\Modules\Objects\Models\NdtObject;
use App\Modules\Welds\Enums\WeldStatus;
use App\Modules\Welds\Models\Weld;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class NotificationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_assignment_creates_inbox_notification_for_assignee(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::query()->where('email', 'admin@example.test')->firstOrFail();
        $role = Role::findByName('Дефектоскопист', 'web');

        [$task, $assigneeUser] = $this->createAssignedTask($admin, $role);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $assigneeUser->id,
            'type' => NotificationType::TaskAssigned->value,
        ]);

        $this->actingAs($assigneeUser)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSeeText($task->task_number);

        $notification = Notification::query()
            ->where('user_id', $assigneeUser->id)
            ->where('type', NotificationType::TaskAssigned->value)
            ->firstOrFail();

        $this->actingAs($assigneeUser)
            ->post(route('notifications.read', $notification))
            ->assertRedirect();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_notifications_api_returns_own_items_and_marks_read(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::query()->where('email', 'admin@example.test')->firstOrFail();
        app(NotificationService::class)->notifyUser(
            $user,
            NotificationType::QueueFailure,
            'queue_failure',
            ['message' => 'Проверка очереди уведомлений.'],
        );

        $response = $this->actingAs($user)
            ->getJson('/api/notifications')
            ->assertOk();

        $notificationId = (int) $response->json('data.0.id');
        $this->assertNotSame(0, $notificationId);

        $this->actingAs($user)
            ->postJson("/api/notifications/{$notificationId}/read")
            ->assertOk()
            ->assertJsonPath('data.notification.id', $notificationId);
    }

    /**
     * @return array{0: NdtTask, 1: User}
     */
    private function createAssignedTask(User $actor, Role $role): array
    {
        $city = City::query()->create([
            'name' => 'Пермь',
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
        $position = Position::query()->create([
            'name' => 'Дефектоскопист',
            'is_active' => true,
            'comment' => null,
        ]);

        $assigneeUser = User::query()->create([
            'name' => 'Исполнитель',
            'email' => 'executor@example.test',
            'password' => 'password',
            'status' => UserStatus::Active,
        ]);
        $assigneeUser->assignRole($role);

        $employee = Employee::query()->create([
            'object_id' => $object->id,
            'position_id' => $position->id,
            'last_name' => 'Иванов',
            'first_name' => 'Иван',
            'middle_name' => null,
            'phone' => null,
            'email' => null,
            'status' => EmployeeStatus::Active,
            'personnel_number' => '200',
        ]);
        $employee->users()->attach($assigneeUser->id);

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
            'status' => NdtRequestStatus::Registered,
        ]);

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

        $method = NdtMethod::query()->where('code', 'rk')->firstOrFail();

        $weld = Weld::query()->create([
            'object_id' => $object->id,
            'title_id' => $title->id,
            'drawing_id' => $drawing->id,
            'line_id' => $line->id,
            'weld_number' => '1',
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

        $task = app(NdtTaskService::class)->create(
            new AssignNdtTaskData(
                taskNumber: 'TASK-1',
                ndtRequestId: $request->id,
                objectId: $object->id,
                ndtMethodId: $method->id,
                assigneeEmployeeId: $employee->id,
                plannedDate: now()->toDateString(),
                priority: null,
                comment: null,
                weldIds: [$weld->id],
            ),
            $actor,
        );

        return [$task, $assigneeUser];
    }
}
