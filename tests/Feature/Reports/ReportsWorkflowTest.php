<?php

declare(strict_types=1);

namespace Tests\Feature\Reports;

use App\Models\User;
use App\Modules\Auth\Enums\UserStatus;
use App\Modules\Documents\Models\File;
use App\Modules\Employees\Enums\EmployeeStatus;
use App\Modules\Employees\Models\Employee;
use App\Modules\Employees\Models\Position;
use App\Modules\Notifications\Enums\NotificationType;
use App\Modules\Objects\Models\City;
use App\Modules\Objects\Models\NdtObject;
use App\Modules\Reports\Enums\ReportStatus;
use App\Modules\Reports\Jobs\ExportExcelJob;
use App\Modules\Reports\Models\ReportJob;
use App\Modules\Reports\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class ReportsWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_queue_and_generate_excel_report_with_notification_and_private_file(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        Storage::fake('private');
        Queue::fake();

        $admin = User::query()->where('email', 'admin@example.test')->firstOrFail();
        $object = $this->createObjectWithCity('Участок отчетов');

        $this->actingAs($admin)
            ->post(route('admin.reports.store'), [
                'report_type' => 'requests',
                'object_id' => $object->id,
                'city_id' => $object->city_id,
                'search' => 'REQ',
                'status' => 'registered',
                'date_from' => '2026-07-01',
                'date_to' => '2026-07-31',
            ])
            ->assertRedirect(route('admin.reports.index'));

        $reportJob = ReportJob::query()->firstOrFail();

        $this->assertSame(ReportStatus::Queued, $reportJob->status);
        Queue::assertPushed(ExportExcelJob::class, static fn (ExportExcelJob $job): bool => $job->reportJobId === $reportJob->getKey());

        (new ExportExcelJob($reportJob->getKey()))->handle(app(ReportService::class));

        $reportJob->refresh();
        $file = File::query()->where('related_type', ReportJob::class)->where('related_id', $reportJob->getKey())->firstOrFail();

        $this->assertSame(ReportStatus::Completed, $reportJob->status);
        Storage::disk('private')->assertExists($file->storage_path);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'type' => NotificationType::ReportReady->value,
        ]);
    }

    public function test_user_cannot_queue_report_for_foreign_object(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $foreignUser = $this->createScopedUser('foreign-reports@example.test', 'Начальник участка', 'Чужой участок');
        $foreignObject = $foreignUser->objectId();
        $otherObject = $this->createObjectWithCity('Чужой объект');

        $this->actingAs($foreignUser)
            ->post(route('admin.reports.store'), [
                'report_type' => 'welds',
                'object_id' => $otherObject->id,
                'search' => 'W-1',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('report_jobs', [
            'requested_by_user_id' => $foreignUser->id,
            'object_id' => $otherObject->id,
        ]);
        $this->assertNotNull($foreignObject);
    }

    public function test_user_from_other_object_cannot_download_generated_report_file(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        Storage::fake('private');

        $admin = User::query()->where('email', 'admin@example.test')->firstOrFail();
        $adminObject = $this->createObjectWithCity('Участок A');
        $otherUser = $this->createScopedUser('download-foreign@example.test', 'Начальник участка', 'Участок B');

        $this->actingAs($admin)
            ->post(route('admin.reports.store'), [
                'report_type' => 'equipment',
                'object_id' => $adminObject->id,
            ])
            ->assertRedirect(route('admin.reports.index'));

        $reportJob = ReportJob::query()->firstOrFail();
        (new ExportExcelJob($reportJob->getKey()))->handle(app(ReportService::class));

        $file = File::query()->where('related_type', ReportJob::class)->where('related_id', $reportJob->getKey())->firstOrFail();

        $this->actingAs($otherUser)
            ->get(route('admin.files.download', $file))
            ->assertForbidden();
    }

    private function createObjectWithCity(string $objectName): NdtObject
    {
        $city = City::query()->create([
            'name' => 'Город '.$objectName,
            'is_active' => true,
            'comment' => null,
        ]);

        return NdtObject::query()->create([
            'city_id' => $city->id,
            'name' => $objectName,
            'code' => null,
            'is_active' => true,
            'comment' => null,
        ]);
    }

    private function createScopedUser(string $email, string $roleName, string $objectName): User
    {
        $object = $this->createObjectWithCity($objectName);
        $position = Position::query()->create([
            'name' => 'Лаборант',
            'is_active' => true,
            'comment' => null,
        ]);

        $user = User::query()->create([
            'name' => $objectName.' пользователь',
            'email' => $email,
            'password' => 'password',
            'status' => UserStatus::Active,
        ]);
        $user->assignRole(Role::findByName($roleName, 'web'));

        $employee = Employee::query()->create([
            'object_id' => $object->id,
            'position_id' => $position->id,
            'last_name' => 'Иванов',
            'first_name' => 'Иван',
            'middle_name' => null,
            'phone' => null,
            'email' => null,
            'status' => EmployeeStatus::Active,
            'personnel_number' => (string) random_int(1000, 9999),
        ]);
        $employee->users()->attach($user->id);

        return $user;
    }
}
