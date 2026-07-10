<?php

declare(strict_types=1);

namespace Tests\Feature\Conclusions;

use App\Models\User;
use App\Modules\Conclusions\Enums\ConclusionStatus;
use App\Modules\Conclusions\Enums\ConclusionVersionStatus;
use App\Modules\Conclusions\Models\Conclusion;
use App\Modules\Conclusions\Models\ConclusionVersion;
use App\Modules\Employees\Enums\EmployeeStatus;
use App\Modules\Employees\Models\Employee;
use App\Modules\Employees\Models\Position;
use App\Modules\NdtRequests\Enums\NdtRequestStatus;
use App\Modules\NdtRequests\Models\NdtRequest;
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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class ConclusionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_prepare_approve_issue_and_generate_pdf_version(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        Storage::fake('private');

        $admin = User::query()->where('email', 'admin@example.test')->firstOrFail();
        $result = $this->createReadyResult();

        $this->actingAs($admin)
            ->post(route('admin.conclusions.store'), [
                'number' => 'CON-001',
                'date' => '2026-07-11',
                'result_ids' => [$result->id],
                'comment' => 'Проект заключения',
            ])
            ->assertRedirect();

        $conclusion = Conclusion::query()->where('number', 'CON-001')->firstOrFail();

        $this->assertSame(ConclusionStatus::Prepared, $conclusion->status);
        $this->assertDatabaseHas('conclusion_items', [
            'conclusion_id' => $conclusion->id,
            'ndt_result_id' => $result->id,
            'sort_order' => 1,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.conclusions.submit', $conclusion), [
                'comment' => 'Отправить на проверку',
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->patch(route('admin.conclusions.approve', $conclusion), [
                'comment' => 'Утверждено',
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->patch(route('admin.conclusions.issue', $conclusion), [
                'basis' => 'Выдача заключения',
            ])
            ->assertRedirect();

        $conclusion->refresh();
        $version = ConclusionVersion::query()->where('conclusion_id', $conclusion->id)->firstOrFail();

        $this->assertSame(ConclusionStatus::Issued, $conclusion->status);
        $this->assertSame(ConclusionVersionStatus::Current, $version->status);
        Storage::disk('private')->assertExists($version->file->storage_path);
        $this->assertDatabaseHas('conclusion_status_history', [
            'conclusion_id' => $conclusion->id,
            'to_status' => ConclusionStatus::Issued->value,
        ]);
    }

    public function test_approved_conclusion_cannot_be_updated_directly(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        Storage::fake('private');

        $admin = User::query()->where('email', 'admin@example.test')->firstOrFail();
        $result = $this->createReadyResult();

        $this->actingAs($admin)
            ->post(route('admin.conclusions.store'), [
                'number' => 'CON-002',
                'date' => '2026-07-11',
                'result_ids' => [$result->id],
                'comment' => null,
            ])
            ->assertRedirect();

        $conclusion = Conclusion::query()->where('number', 'CON-002')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.conclusions.submit', $conclusion));
        $this->actingAs($admin)->patch(route('admin.conclusions.approve', $conclusion));

        $this->actingAs($admin)
            ->patch(route('admin.conclusions.update', $conclusion), [
                'number' => 'CON-002-EDIT',
                'date' => '2026-07-11',
                'comment' => 'Попытка прямого редактирования',
            ])
            ->assertForbidden();
    }

    public function test_new_version_supersedes_previous_current_version(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        Storage::fake('private');

        $admin = User::query()->where('email', 'admin@example.test')->firstOrFail();
        $result = $this->createReadyResult();

        $this->actingAs($admin)
            ->post(route('admin.conclusions.store'), [
                'number' => 'CON-003',
                'date' => '2026-07-11',
                'result_ids' => [$result->id],
                'comment' => null,
            ])
            ->assertRedirect();

        $conclusion = Conclusion::query()->where('number', 'CON-003')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.conclusions.submit', $conclusion));
        $this->actingAs($admin)->patch(route('admin.conclusions.approve', $conclusion));
        $this->actingAs($admin)->patch(route('admin.conclusions.issue', $conclusion), [
            'basis' => 'Первичная выдача',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.conclusions.versions.store', $conclusion), [
                'basis' => 'Исправленная версия',
            ])
            ->assertRedirect();

        $versions = $conclusion->refresh()->versions()->orderBy('version_number')->get();

        $this->assertCount(2, $versions);
        $this->assertSame(ConclusionVersionStatus::Superseded, $versions[0]->status);
        $this->assertSame(ConclusionVersionStatus::Current, $versions[1]->status);
    }

    public function test_replacement_creates_new_draft_and_marks_original_replaced(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        Storage::fake('private');

        $admin = User::query()->where('email', 'admin@example.test')->firstOrFail();
        $result = $this->createReadyResult();

        $this->actingAs($admin)
            ->post(route('admin.conclusions.store'), [
                'number' => 'CON-004',
                'date' => '2026-07-11',
                'result_ids' => [$result->id],
                'comment' => null,
            ])
            ->assertRedirect();

        $conclusion = Conclusion::query()->where('number', 'CON-004')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.conclusions.replace', $conclusion), [
                'number' => 'CON-004-R1',
                'date' => '2026-07-12',
                'reason' => 'Исправление номера',
                'comment' => 'Заменяющее заключение',
            ])
            ->assertRedirect();

        $conclusion->refresh();
        $replacement = Conclusion::query()->where('number', 'CON-004-R1')->firstOrFail();

        $this->assertSame(ConclusionStatus::Replaced, $conclusion->status);
        $this->assertSame(ConclusionStatus::Draft, $replacement->status);
        $this->assertSame($conclusion->object_id, $replacement->object_id);
        $this->assertSame($conclusion->ndt_method_id, $replacement->ndt_method_id);
        $this->assertCount(1, $replacement->items);
    }

    private function createReadyResult(): NdtResult
    {
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

        $position = Position::query()->create([
            'name' => 'Лаборант',
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
            'personnel_number' => '100',
        ]);

        $method = NdtMethod::query()->create([
            'code' => NdtMethodCode::RK,
            'name' => 'РК',
            'is_active' => true,
            'comment' => null,
        ]);

        $request = NdtRequest::query()->create([
            'request_number' => 'REQ-001',
            'request_date' => '2026-07-10',
            'organization_id' => null,
            'object_id' => $object->id,
            'title_id' => null,
            'priority' => null,
            'due_date' => null,
            'basis' => null,
            'comment' => null,
            'status' => NdtRequestStatus::Accepted,
        ]);

        $weld = Weld::query()->create([
            'object_id' => $object->id,
            'title_id' => null,
            'drawing_id' => null,
            'line_id' => null,
            'weld_number' => 'W-001',
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
            'status' => WeldStatus::WaitingAnalysis,
        ]);
        $weld->ndtMethods()->attach($method->id);

        $task = NdtTask::query()->create([
            'task_number' => 'TASK-001',
            'ndt_request_id' => $request->id,
            'object_id' => $object->id,
            'ndt_method_id' => $method->id,
            'assignee_employee_id' => $employee->id,
            'planned_date' => '2026-07-11',
            'priority' => null,
            'comment' => null,
            'status' => NdtTaskStatus::Created->value,
        ]);

        return NdtResult::query()->create([
            'ndt_task_id' => $task->id,
            'weld_id' => $weld->id,
            'ndt_method_id' => $method->id,
            'executor_employee_id' => $employee->id,
            'equipment_id' => null,
            'normative_document_id' => null,
            'control_date' => '2026-07-11',
            'analyzed_at' => null,
            'result_text' => 'Годен',
            'comment' => null,
            'status' => NdtResultStatus::ReadyForConclusion->value,
        ]);
    }
}
