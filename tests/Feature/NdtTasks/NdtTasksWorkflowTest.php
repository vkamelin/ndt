<?php

declare(strict_types=1);

namespace Tests\Feature\NdtTasks;

use App\Models\User;
use App\Modules\Auth\Enums\UserStatus;
use App\Modules\Employees\Enums\EmployeeStatus;
use App\Modules\Employees\Models\Employee;
use App\Modules\Employees\Models\Position;
use App\Modules\NdtRequests\Models\NdtRequest;
use App\Modules\NdtTasks\Enums\NdtMethodCode;
use App\Modules\NdtTasks\Enums\NdtTaskStatus;
use App\Modules\NdtTasks\Models\NdtMethod;
use App\Modules\NdtTasks\Models\NdtTask;
use App\Modules\Objects\Models\City;
use App\Modules\Objects\Models\NdtObject;
use App\Modules\Welds\Models\Weld;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class NdtTasksWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_chief_can_assign_methods_and_create_task_with_items(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::query()->where('email', 'admin@example.test')->firstOrFail();
        $city = City::query()->create([
            'name' => 'Екатеринбург',
            'is_active' => true,
            'comment' => null,
        ]);
        $object = NdtObject::query()->create([
            'city_id' => $city->id,
            'name' => 'Участок 6',
            'code' => 'U6',
            'is_active' => true,
            'comment' => null,
        ]);
        $position = Position::query()->create([
            'name' => 'Дефектоскопист',
            'is_active' => true,
            'comment' => null,
        ]);
        $executorUser = User::query()->create([
            'name' => 'Исполнитель',
            'email' => 'executor@example.test',
            'password' => 'password',
            'status' => UserStatus::Active,
        ]);
        $executorUser->assignRole(Role::findByName('Дефектоскопист', 'web'));

        $executor = Employee::query()->create([
            'object_id' => $object->id,
            'position_id' => $position->id,
            'last_name' => 'Иванов',
            'first_name' => 'Иван',
            'middle_name' => null,
            'phone' => null,
            'email' => null,
            'status' => EmployeeStatus::Active,
            'personnel_number' => '201',
        ]);
        $executor->users()->sync([$executorUser->id]);

        $request = NdtRequest::query()->create([
            'request_number' => 'NR-200',
            'request_date' => '2026-07-09',
            'organization_id' => null,
            'object_id' => $object->id,
            'title_id' => null,
            'priority' => 'Высокий',
            'due_date' => '2026-07-20',
            'basis' => 'Плановый контроль',
            'comment' => null,
            'status' => 'draft',
        ]);
        $method = NdtMethod::query()->where('code', NdtMethodCode::RK->value)->firstOrFail();
        $weld = Weld::query()->create([
            'object_id' => $object->id,
            'title_id' => null,
            'drawing_id' => null,
            'line_id' => null,
            'weld_number' => 'W-100',
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
            'status' => 'created',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.welds.methods.sync', $weld), [
                'method_ids' => [$method->id],
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->post(route('admin.ndt-tasks.store'), [
                'task_number' => 'NT-001',
                'ndt_request_id' => $request->id,
                'object_id' => $object->id,
                'ndt_method_id' => $method->id,
                'assignee_employee_id' => $executor->id,
                'planned_date' => '2026-07-10',
                'priority' => 'Высокий',
                'comment' => 'Первичное задание',
                'weld_ids' => [$weld->id],
            ])
            ->assertRedirect();

        $task = NdtTask::query()->where('task_number', 'NT-001')->firstOrFail();

        $this->assertSame(NdtTaskStatus::Assigned, $task->status);
        $this->assertDatabaseHas('ndt_task_items', [
            'ndt_task_id' => $task->id,
            'weld_id' => $weld->id,
            'position_number' => 1,
        ]);
        $this->assertDatabaseHas('ndt_task_status_history', [
            'ndt_task_id' => $task->id,
            'to_status' => NdtTaskStatus::Created->value,
        ]);
        $this->assertDatabaseHas('ndt_task_status_history', [
            'ndt_task_id' => $task->id,
            'to_status' => NdtTaskStatus::Assigned->value,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'subject_type' => NdtTask::class,
            'subject_id' => $task->id,
            'event' => 'ndt_task.created',
        ]);
    }

    public function test_executor_sees_only_own_tasks_and_can_complete_workflow(): void
    {
        $this->seed(DatabaseSeeder::class);

        $chief = User::query()->where('email', 'admin@example.test')->firstOrFail();
        $city = City::query()->create([
            'name' => 'Пермь',
            'is_active' => true,
            'comment' => null,
        ]);
        $object = NdtObject::query()->create([
            'city_id' => $city->id,
            'name' => 'Участок 7',
            'code' => 'U7',
            'is_active' => true,
            'comment' => null,
        ]);
        $position = Position::query()->create([
            'name' => 'Инженер',
            'is_active' => true,
            'comment' => null,
        ]);
        $executorUser = User::query()->create([
            'name' => 'Исполнитель',
            'email' => 'executor2@example.test',
            'password' => 'password',
            'status' => UserStatus::Active,
        ]);
        $executorUser->assignRole(Role::findByName('Дефектоскопист', 'web'));
        $otherUser = User::query()->create([
            'name' => 'Другой исполнитель',
            'email' => 'other@example.test',
            'password' => 'password',
            'status' => UserStatus::Active,
        ]);
        $otherUser->assignRole(Role::findByName('Дефектоскопист', 'web'));

        $executor = Employee::query()->create([
            'object_id' => $object->id,
            'position_id' => $position->id,
            'last_name' => 'Петров',
            'first_name' => 'Петр',
            'middle_name' => null,
            'phone' => null,
            'email' => null,
            'status' => EmployeeStatus::Active,
            'personnel_number' => '301',
        ]);
        $executor->users()->sync([$executorUser->id]);

        $otherEmployee = Employee::query()->create([
            'object_id' => $object->id,
            'position_id' => $position->id,
            'last_name' => 'Сидоров',
            'first_name' => 'Сидор',
            'middle_name' => null,
            'phone' => null,
            'email' => null,
            'status' => EmployeeStatus::Active,
            'personnel_number' => '302',
        ]);
        $otherEmployee->users()->sync([$otherUser->id]);

        $request = NdtRequest::query()->create([
            'request_number' => 'NR-300',
            'request_date' => '2026-07-09',
            'organization_id' => null,
            'object_id' => $object->id,
            'title_id' => null,
            'priority' => null,
            'due_date' => null,
            'basis' => null,
            'comment' => null,
            'status' => 'draft',
        ]);
        $method = NdtMethod::query()->where('code', NdtMethodCode::RK->value)->firstOrFail();
        $weldOne = Weld::query()->create([
            'object_id' => $object->id,
            'title_id' => null,
            'drawing_id' => null,
            'line_id' => null,
            'weld_number' => 'W-200',
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
            'status' => 'created',
        ]);
        $weldTwo = Weld::query()->create([
            'object_id' => $object->id,
            'title_id' => null,
            'drawing_id' => null,
            'line_id' => null,
            'weld_number' => 'W-201',
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
            'status' => 'created',
        ]);

        $this->actingAs($chief)
            ->patch(route('admin.welds.methods.sync', $weldOne), [
                'method_ids' => [$method->id],
            ])
            ->assertRedirect();

        $this->actingAs($chief)
            ->patch(route('admin.welds.methods.sync', $weldTwo), [
                'method_ids' => [$method->id],
            ])
            ->assertRedirect();

        $taskOne = NdtTask::query()->create([
            'task_number' => 'NT-200',
            'ndt_request_id' => $request->id,
            'object_id' => $object->id,
            'ndt_method_id' => $method->id,
            'assignee_employee_id' => $executor->id,
            'planned_date' => '2026-07-11',
            'priority' => null,
            'comment' => null,
            'status' => NdtTaskStatus::Assigned,
        ]);
        $taskOne->items()->create([
            'weld_id' => $weldOne->id,
            'position_number' => 1,
        ]);
        $taskOne->statusHistory()->create([
            'from_status' => null,
            'to_status' => NdtTaskStatus::Created->value,
            'changed_by_user_id' => $chief->id,
            'comment' => null,
        ]);
        $taskOne->statusHistory()->create([
            'from_status' => NdtTaskStatus::Created->value,
            'to_status' => NdtTaskStatus::Assigned->value,
            'changed_by_user_id' => $chief->id,
            'comment' => null,
        ]);

        $taskTwo = NdtTask::query()->create([
            'task_number' => 'NT-201',
            'ndt_request_id' => $request->id,
            'object_id' => $object->id,
            'ndt_method_id' => $method->id,
            'assignee_employee_id' => $otherEmployee->id,
            'planned_date' => '2026-07-12',
            'priority' => null,
            'comment' => null,
            'status' => NdtTaskStatus::Assigned,
        ]);
        $taskTwo->items()->create([
            'weld_id' => $weldTwo->id,
            'position_number' => 1,
        ]);
        $taskTwo->statusHistory()->create([
            'from_status' => null,
            'to_status' => NdtTaskStatus::Created->value,
            'changed_by_user_id' => $chief->id,
            'comment' => null,
        ]);
        $taskTwo->statusHistory()->create([
            'from_status' => NdtTaskStatus::Created->value,
            'to_status' => NdtTaskStatus::Assigned->value,
            'changed_by_user_id' => $chief->id,
            'comment' => null,
        ]);

        $this->actingAs($executorUser)
            ->get(route('admin.ndt-tasks.index'))
            ->assertOk()
            ->assertSeeText('NT-200')
            ->assertDontSeeText('NT-201');

        $this->actingAs($executorUser)
            ->get(route('admin.ndt-tasks.show', $taskTwo))
            ->assertForbidden();

        $this->actingAs($executorUser)
            ->patch(route('admin.ndt-tasks.status.accept', $taskOne), [
                'comment' => 'Принято',
            ])
            ->assertRedirect();

        $taskOne->refresh();
        $this->assertSame(NdtTaskStatus::Accepted, $taskOne->status);

        $this->actingAs($executorUser)
            ->patch(route('admin.ndt-tasks.status.start', $taskOne), [
                'comment' => 'Начал работу',
            ])
            ->assertRedirect();

        $taskOne->refresh();
        $this->assertSame(NdtTaskStatus::InWork, $taskOne->status);

        $this->actingAs($executorUser)
            ->patch(route('admin.ndt-tasks.status.complete', $taskOne), [
                'comment' => 'Готово',
            ])
            ->assertRedirect();

        $taskOne->refresh();
        $this->assertSame(NdtTaskStatus::Completed, $taskOne->status);
        $this->assertDatabaseHas('ndt_task_status_history', [
            'ndt_task_id' => $taskOne->id,
            'to_status' => NdtTaskStatus::Completed->value,
        ]);
    }
}
