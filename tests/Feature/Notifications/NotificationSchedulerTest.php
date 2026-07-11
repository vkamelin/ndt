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
use App\Modules\Notifications\Enums\NotificationDeliveryStatus;
use App\Modules\Notifications\Enums\NotificationType;
use App\Modules\Notifications\Models\Notification;
use App\Modules\Notifications\Models\NotificationDelivery;
use App\Modules\Objects\Models\City;
use App\Modules\Objects\Models\NdtObject;
use App\Modules\Welds\Enums\WeldStatus;
use App\Modules\Welds\Models\Weld;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class NotificationSchedulerTest extends TestCase
{
    use RefreshDatabase;

    public function test_overdue_task_command_creates_notification(): void
    {
        $this->seed(DatabaseSeeder::class);

        [$task, $assigneeUser] = $this->createAssignedTask();
        $task->forceFill(['planned_date' => now()->subDay()->toDateString()])->save();

        Artisan::call('notifications:check-overdue-tasks');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $assigneeUser->id,
            'type' => NotificationType::TaskOverdue->value,
        ]);
    }

    public function test_queue_health_command_alerts_admin_when_deliveries_are_stuck(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::query()->where('email', 'admin@example.test')->firstOrFail();
        $notification = Notification::query()->create([
            'user_id' => $admin->id,
            'notification_template_id' => null,
            'type' => NotificationType::QueueFailure->value,
            'title' => 'Пробное уведомление',
            'body' => 'Пробная проверка.',
            'data' => ['fingerprint' => 'test', 'context' => []],
            'read_at' => null,
        ]);

        NotificationDelivery::query()->create([
            'notification_id' => $notification->id,
            'channel' => 'email',
            'recipient_address' => $admin->email,
            'status' => NotificationDeliveryStatus::Queued->value,
            'queued_at' => now()->subHour(),
            'sent_at' => null,
            'failed_at' => null,
            'error_message' => null,
            'meta' => null,
        ]);

        Artisan::call('notifications:check-queue');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'type' => NotificationType::QueueFailure->value,
        ]);
    }

    /**
     * @return array{0: NdtTask, 1: User}
     */
    private function createAssignedTask(): array
    {
        $admin = User::query()->where('email', 'admin@example.test')->firstOrFail();
        $role = Role::findByName('Дефектоскопист', 'web');

        $city = City::query()->create([
            'name' => 'Казань',
            'is_active' => true,
            'comment' => null,
        ]);
        $object = NdtObject::query()->create([
            'city_id' => $city->id,
            'name' => 'Участок 7',
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
            'email' => 'executor2@example.test',
            'password' => 'password',
            'status' => UserStatus::Active,
        ]);
        $assigneeUser->assignRole($role);

        $employee = Employee::query()->create([
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
        $employee->users()->attach($assigneeUser->id);

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
            'status' => NdtRequestStatus::Registered,
        ]);

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
            'name' => 'Категория 2',
            'is_active' => true,
            'comment' => null,
        ]);
        $medium = Medium::query()->create([
            'name' => 'Газ',
            'is_active' => true,
            'comment' => null,
        ]);
        $normativeDocument = NormativeDocument::query()->create([
            'name' => 'НД 2',
            'is_active' => true,
            'comment' => null,
        ]);

        $method = NdtMethod::query()->where('code', 'rk')->firstOrFail();

        $weld = Weld::query()->create([
            'object_id' => $object->id,
            'title_id' => $title->id,
            'drawing_id' => $drawing->id,
            'line_id' => $line->id,
            'weld_number' => '2',
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
                taskNumber: 'TASK-2',
                ndtRequestId: $request->id,
                objectId: $object->id,
                ndtMethodId: $method->id,
                assigneeEmployeeId: $employee->id,
                plannedDate: now()->toDateString(),
                priority: null,
                comment: null,
                weldIds: [$weld->id],
            ),
            $admin,
        );

        return [$task, $assigneeUser];
    }
}
