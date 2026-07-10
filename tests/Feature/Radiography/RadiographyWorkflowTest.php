<?php

declare(strict_types=1);

namespace Tests\Feature\Radiography;

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
use App\Modules\Welds\Enums\WeldStatus;
use App\Modules\Welds\Models\Weld;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class RadiographyWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_engineer_can_create_radiography_card_and_work_with_materials(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $engineer = User::query()->create([
            'name' => 'Инженер НК',
            'email' => 'rk-engineer@example.test',
            'password' => 'password',
            'status' => UserStatus::Active,
        ]);
        $engineer->assignRole(Role::findByName('Инженер НК / Дешифровщик', 'web'));

        $city = City::query()->create([
            'name' => 'Екатеринбург',
            'is_active' => true,
            'comment' => null,
        ]);
        $object = NdtObject::query()->create([
            'city_id' => $city->id,
            'name' => 'Участок РК',
            'code' => 'RK',
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
            'last_name' => 'Петров',
            'first_name' => 'Петр',
            'middle_name' => null,
            'phone' => null,
            'email' => null,
            'status' => EmployeeStatus::Active,
            'personnel_number' => '9001',
        ]);
        $employee->users()->sync([$engineer->id]);

        $method = NdtMethod::query()->where('code', NdtMethodCode::RK->value)->firstOrFail();
        $weld = Weld::query()->create([
            'object_id' => $object->id,
            'title_id' => null,
            'drawing_id' => null,
            'line_id' => null,
            'weld_number' => 'W-RK-1',
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
            'task_number' => 'TASK-RK-1',
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
            'result_text' => 'Первичный контроль',
            'comment' => null,
            'status' => NdtResultStatus::Created,
        ]);

        $this->actingAs($engineer)
            ->post(route('admin.radiography.store'), [
                'ndt_result_id' => $ndtResult->id,
                'film_type_id' => null,
                'barcode' => 'RT-001',
                'conclusion_number' => 'CON-1',
                'control_date' => now()->toDateString(),
                'conclusion_date' => now()->toDateString(),
                'archive_location' => 'Архив 1',
                'result_text' => 'Снимки готовы',
                'comment' => 'Карта РК',
            ])
            ->assertRedirect();

        $rtResult = RtResult::query()->where('ndt_result_id', $ndtResult->id)->firstOrFail();
        $this->assertSame(RtStatus::Assigned, $rtResult->status);

        $this->actingAs($engineer)
            ->post(route('admin.radiography.films.store', $rtResult), [
                'film_type_id' => null,
                'barcode' => 'FILM-1',
                'position_number' => 1,
                'comment' => 'Пленка №1',
            ])
            ->assertRedirect();

        $film = $rtResult->films()->firstOrFail();

        $this->actingAs($engineer)
            ->post(route('admin.radiography.images.store', $film), [
                'file_id' => null,
                'sequence_number' => 1,
                'captured_at' => now()->toDateTimeString(),
                'comment' => 'Снимок',
            ])
            ->assertRedirect();

        $this->actingAs($engineer)
            ->post(route('admin.radiography.exposures.store', $film), [
                'rt_result_id' => $rtResult->id,
                'exposure_number' => 1,
                'exposed_at' => now()->toDateTimeString(),
                'comment' => 'Экспозиция',
            ])
            ->assertRedirect();

        $rtResult->refresh();
        $this->assertSame(RtStatus::Shot, $rtResult->status);

        $this->actingAs($engineer)
            ->post(route('admin.radiography.densities.store', $rtResult), [
                'rt_film_id' => $film->id,
                'density' => '2.400',
                'minimum_density' => '2.100',
                'maximum_density' => '2.800',
                'measured_at' => now()->toDateTimeString(),
                'comment' => 'Норма',
            ])
            ->assertRedirect();

        $this->actingAs($engineer)
            ->post(route('admin.radiography.reshoots.store', $rtResult), [
                'rt_film_id' => $film->id,
                'reason' => 'Пересвет на участке',
                'reshot_at' => now()->toDateTimeString(),
                'comment' => 'Нужно повторить',
            ])
            ->assertRedirect();

        $rtResult->refresh();
        $this->assertSame(RtStatus::NeedsReshoot, $rtResult->status);

        $this->actingAs($engineer)
            ->patch(route('admin.radiography.status.update', $rtResult), [
                'status' => RtStatus::ReshootDone->value,
                'comment' => 'Пересвет выполнен',
            ])
            ->assertRedirect();

        $this->actingAs($engineer)
            ->patch(route('admin.radiography.status.update', $rtResult), [
                'status' => RtStatus::Decoded->value,
                'comment' => 'Дешифровка завершена',
            ])
            ->assertRedirect();

        $this->actingAs($engineer)
            ->post(route('admin.radiography.archive-items.store', $rtResult), [
                'rt_film_id' => $film->id,
                'file_id' => null,
                'register_number' => 'REG-1',
                'archive_location' => 'Архив 1',
                'archived_at' => now()->toDateTimeString(),
                'comment' => 'Передано в архив',
            ])
            ->assertRedirect();

        $rtResult->refresh();

        $this->assertSame(RtStatus::Archived, $rtResult->status);
        $this->assertDatabaseHas('rt_films', [
            'rt_result_id' => $rtResult->id,
            'barcode' => 'FILM-1',
        ]);
        $this->assertDatabaseHas('rt_images', [
            'rt_film_id' => $film->id,
            'sequence_number' => 1,
        ]);
        $this->assertDatabaseHas('rt_density_measurements', [
            'rt_result_id' => $rtResult->id,
        ]);
        $this->assertDatabaseHas('rt_reshoots', [
            'rt_result_id' => $rtResult->id,
            'reason' => 'Пересвет на участке',
        ]);
        $this->assertDatabaseHas('rt_archive_items', [
            'rt_result_id' => $rtResult->id,
            'register_number' => 'REG-1',
        ]);
    }
}
