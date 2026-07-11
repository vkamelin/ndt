<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use App\Modules\Auth\Enums\UserStatus;
use App\Modules\Employees\Enums\EmployeeStatus;
use App\Modules\Employees\Models\Employee;
use App\Modules\Employees\Models\Position;
use App\Modules\NdtResults\Enums\NdtResultStatus;
use App\Modules\NdtTasks\Enums\NdtMethodCode;
use App\Modules\NdtTasks\Enums\NdtTaskStatus;
use App\Modules\NdtTasks\Models\NdtMethod;
use App\Modules\NdtTasks\Models\NdtTask;
use App\Modules\NdtTasks\Models\NdtTaskItem;
use App\Modules\Objects\Models\City;
use App\Modules\Objects\Models\NdtObject;
use App\Modules\Welds\Models\Weld;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class ApiMobileTasksTest extends TestCase
{
    use RefreshDatabase;

    public function test_defectoscopist_can_work_with_mobile_tasks_and_results(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::query()->create([
            'name' => 'Исполнитель',
            'email' => 'api-worker@example.test',
            'password' => 'password',
            'status' => UserStatus::Active,
        ]);
        $user->assignRole(Role::findByName('Дефектоскопист', 'web'));

        $city = City::query()->create([
            'name' => 'Екатеринбург',
            'is_active' => true,
            'comment' => null,
        ]);
        $object = NdtObject::query()->create([
            'city_id' => $city->id,
            'name' => 'Участок PWA',
            'code' => 'PWA-1',
            'is_active' => true,
            'comment' => null,
        ]);
        $position = Position::query()->create([
            'name' => 'Дефектоскопист',
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
            'personnel_number' => '301',
        ]);
        $employee->users()->sync([$user->id]);

        $method = NdtMethod::query()->where('code', NdtMethodCode::RK->value)->firstOrFail();
        $weld = Weld::query()->create([
            'object_id' => $object->id,
            'title_id' => null,
            'drawing_id' => null,
            'line_id' => null,
            'weld_number' => 'W-1000',
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

        $task = NdtTask::query()->create([
            'task_number' => 'NT-1000',
            'ndt_request_id' => null,
            'object_id' => $object->id,
            'ndt_method_id' => $method->id,
            'assignee_employee_id' => $employee->id,
            'planned_date' => '2026-07-11',
            'priority' => 'Высокий',
            'comment' => null,
            'status' => NdtTaskStatus::Assigned->value,
        ]);
        $item = NdtTaskItem::query()->create([
            'ndt_task_id' => $task->id,
            'weld_id' => $weld->id,
            'position_number' => 1,
        ]);

        $token = $this->loginToken('api-worker@example.test');

        $this->getJson('/api/mobile/tasks', [
            'Authorization' => 'Bearer '.$token,
        ])->assertOk()
            ->assertJsonPath('data.0.task_number', 'NT-1000');

        $this->postJson('/api/mobile/tasks/'.$task->id.'/accept', [
            'comment' => 'Принял в работу',
        ], [
            'Authorization' => 'Bearer '.$token,
        ])->assertOk()
            ->assertJsonPath('data.status', 'accepted');

        $this->postJson('/api/mobile/tasks/'.$task->id.'/items/'.$item->id.'/complete', [
            'ndt_method_id' => $method->id,
            'control_date' => '2026-07-11',
            'result_text' => 'Контроль выполнен',
            'comment' => 'Без замечаний',
        ], [
            'Authorization' => 'Bearer '.$token,
        ])->assertCreated()
            ->assertJsonPath('data.status', NdtResultStatus::Created->value);

        $this->postJson('/api/mobile/tasks/'.$task->id.'/finish', [
            'comment' => 'Завершил задание',
        ], [
            'Authorization' => 'Bearer '.$token,
        ])->assertOk()
            ->assertJsonPath('data.status', NdtTaskStatus::Completed->value);

        $this->assertDatabaseHas('ndt_results', [
            'ndt_task_id' => $task->id,
            'weld_id' => $weld->id,
        ]);
    }

    private function loginToken(string $email): string
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => $email,
            'password' => 'password',
        ]);

        return (string) $response->json('data.token');
    }
}
