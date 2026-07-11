<?php

declare(strict_types=1);

namespace Tests\Feature\NdtRequests;

use App\Models\User;
use App\Modules\Auth\Enums\UserStatus;
use App\Modules\Employees\Enums\EmployeeStatus;
use App\Modules\Employees\Models\Employee;
use App\Modules\Employees\Models\Position;
use App\Modules\NdtRequests\Enums\NdtRequestStatus;
use App\Modules\NdtRequests\Models\NdtRequest;
use App\Modules\Objects\Models\City;
use App\Modules\Objects\Models\NdtObject;
use App\Modules\Organizations\Models\Organization;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class NdtRequestsWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_chief_can_create_request_with_weld_rows_and_auto_registers_welds(): void
    {
        $this->seed(DatabaseSeeder::class);

        $chief = User::factory()->create([
            'name' => 'Начальник участка',
            'email' => 'chief-requests@example.test',
            'password' => 'password',
            'status' => UserStatus::Active,
        ]);
        $chief->assignRole(Role::findByName('Начальник участка', 'web'));

        $city = City::query()->create([
            'name' => 'Екатеринбург',
            'is_active' => true,
            'comment' => null,
        ]);
        $organization = Organization::query()->create([
            'name' => 'ООО Контроль',
            'is_active' => true,
            'comment' => null,
        ]);
        $object = NdtObject::query()->create([
            'city_id' => $city->id,
            'organization_id' => $organization->id,
            'name' => 'Участок 8',
            'code' => 'U8',
            'is_active' => true,
            'comment' => null,
        ]);
        $position = Position::query()->create([
            'name' => 'Инженер',
            'is_active' => true,
            'comment' => null,
        ]);

        Employee::query()->create([
            'object_id' => $object->id,
            'position_id' => $position->id,
            'last_name' => 'Иванов',
            'first_name' => 'Иван',
            'middle_name' => null,
            'phone' => null,
            'email' => null,
            'status' => EmployeeStatus::Active,
            'personnel_number' => '501',
        ])->users()->sync([$chief->id]);

        $this->actingAs($chief)
            ->post(route('admin.ndt-requests.store'), [
                'request_number' => 'NR-100',
                'request_date' => '2026-07-11',
                'title_id' => null,
                'priority' => 'Высокий',
                'due_date' => '2026-07-15',
                'basis' => 'Плановый контроль',
                'comment' => 'Создание с ручным вводом стыков',
                'welds' => [
                    [
                        'weld_number' => '8-01',
                        'diameter' => '325',
                        'thickness' => '12',
                        'welded_at' => '2026-07-10',
                        'pwht' => false,
                    ],
                    [
                        'weld_number' => '8-02',
                        'diameter' => '273',
                        'thickness' => '10',
                        'welded_at' => '2026-07-10',
                        'pwht' => true,
                    ],
                ],
            ])
            ->assertRedirect(route('admin.ndt-requests.index'));

        $request = NdtRequest::query()->where('request_number', 'NR-100')->firstOrFail();

        $this->assertSame($object->id, $request->object_id);
        $this->assertSame($organization->id, $request->organization_id);
        $this->assertSame(NdtRequestStatus::Draft, $request->status);
        $this->assertDatabaseHas('welds', [
            'object_id' => $object->id,
            'weld_number' => '8-01',
        ]);
        $this->assertDatabaseHas('welds', [
            'object_id' => $object->id,
            'weld_number' => '8-02',
        ]);
        $this->assertDatabaseHas('ndt_request_items', [
            'ndt_request_id' => $request->id,
        ]);
        $this->assertSame(2, $request->fresh()->welds()->count());
    }

    public function test_chief_can_preview_and_import_request_from_csv(): void
    {
        $this->seed(DatabaseSeeder::class);
        Storage::fake('local');

        $chief = User::factory()->create([
            'name' => 'Начальник участка',
            'email' => 'chief-import@example.test',
            'password' => 'password',
            'status' => UserStatus::Active,
        ]);
        $chief->assignRole(Role::findByName('Начальник участка', 'web'));

        $city = City::query()->create([
            'name' => 'Пермь',
            'is_active' => true,
            'comment' => null,
        ]);
        $organization = Organization::query()->create([
            'name' => 'ООО Север',
            'is_active' => true,
            'comment' => null,
        ]);
        $object = NdtObject::query()->create([
            'city_id' => $city->id,
            'organization_id' => $organization->id,
            'name' => 'Участок 9',
            'code' => 'U9',
            'is_active' => true,
            'comment' => null,
        ]);
        $position = Position::query()->create([
            'name' => 'Инженер',
            'is_active' => true,
            'comment' => null,
        ]);

        Employee::query()->create([
            'object_id' => $object->id,
            'position_id' => $position->id,
            'last_name' => 'Петров',
            'first_name' => 'Петр',
            'middle_name' => null,
            'phone' => null,
            'email' => null,
            'status' => EmployeeStatus::Active,
            'personnel_number' => '601',
        ])->users()->sync([$chief->id]);

        $csv = <<<'CSV'
Номер стыка;Диаметр;Толщина;Дата сварки;PWHT
9-01;219;8;2026-07-11;1
9-02;219;8;2026-07-11;0
CSV;

        $this->actingAs($chief)
            ->post(route('admin.ndt-requests.import.preview'), [
                'request_number' => 'NR-200',
                'request_date' => '2026-07-11',
                'priority' => 'Средний',
                'due_date' => '2026-07-18',
                'basis' => 'Импорт из CSV',
                'comment' => 'Предпросмотр импорта',
                'file' => UploadedFile::fake()->createWithContent('request.csv', $csv),
            ])
            ->assertOk()
            ->assertSee('Предпросмотр импорта');

        $files = Storage::disk('local')->files('ndt-request-imports');
        $this->assertCount(1, $files);
        $importToken = basename($files[0], '.json');

        $this->actingAs($chief)
            ->post(route('admin.ndt-requests.import.store'), [
                'import_token' => $importToken,
            ])
            ->assertRedirect(route('admin.ndt-requests.index'));

        $request = NdtRequest::query()->where('request_number', 'NR-200')->firstOrFail();

        $this->assertSame($object->id, $request->object_id);
        $this->assertSame($organization->id, $request->organization_id);
        $this->assertSame(2, $request->fresh()->welds()->count());
        $this->assertDatabaseHas('welds', [
            'object_id' => $object->id,
            'weld_number' => '9-01',
        ]);
        $this->assertDatabaseHas('welds', [
            'object_id' => $object->id,
            'weld_number' => '9-02',
        ]);
    }
}
