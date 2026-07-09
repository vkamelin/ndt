<?php

declare(strict_types=1);

namespace Tests\Feature\Stage5;

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
use App\Modules\Welds\Models\Weld;
use App\Modules\Welds\Models\Welder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class Stage5WorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_organizations_welds_requests_and_relations(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $admin = User::query()->where('email', 'admin@example.test')->firstOrFail();
        $city = City::query()->create([
            'name' => 'Екатеринбург',
            'is_active' => true,
            'comment' => null,
        ]);
        $object = NdtObject::query()->create([
            'city_id' => $city->id,
            'name' => 'Участок 5',
            'code' => 'U5',
            'is_active' => true,
            'comment' => null,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.organizations.store'), [
                'name' => 'ООО Контроль',
                'comment' => 'Заказчик',
                'is_active' => true,
            ])
            ->assertRedirect();

        $organization = Organization::query()->where('name', 'ООО Контроль')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.organizations.contacts.store', $organization), [
                'name' => 'Иван Петров',
                'position' => 'Технический директор',
                'phone' => '+7 900 000-11-11',
                'email' => 'contact@example.test',
                'comment' => 'Основной контакт',
                'is_primary' => true,
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->post(route('admin.organizations.laboratories.store', $organization), [
                'name' => 'Лаборатория НК',
                'comment' => 'Внутренний профиль',
                'is_active' => true,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('organization_contacts', [
            'organization_id' => $organization->id,
            'name' => 'Иван Петров',
        ]);
        $this->assertDatabaseHas('laboratories', [
            'organization_id' => $organization->id,
            'name' => 'Лаборатория НК',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.welds.store'), [
                'object_id' => $object->id,
                'weld_number' => '1-01',
                'title_id' => null,
                'drawing_id' => null,
                'line_id' => null,
                'diameter' => '325',
                'thickness' => '12',
                'material_1_id' => null,
                'material_2_id' => null,
                'welded_at' => '2026-07-09',
            ])
            ->assertRedirect();

        $weld = Weld::query()->where('weld_number', '1-01')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.welders.store'), [
                'name' => 'Сварщик А',
                'stamp' => 'A1',
                'employee_id' => null,
                'comment' => 'Полевой сварщик',
                'is_active' => true,
            ])
            ->assertRedirect();

        $welder = Welder::query()->where('stamp', 'A1')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.welds.welders.attach', $weld), [
                'welder_id' => $welder->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('weld_welders', [
            'weld_id' => $weld->id,
            'welder_id' => $welder->id,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.ndt-requests.store'), [
                'request_number' => 'NR-001',
                'request_date' => '2026-07-09',
                'organization_id' => $organization->id,
                'object_id' => $object->id,
                'title_id' => null,
                'priority' => 'Высокий',
                'due_date' => '2026-07-15',
                'basis' => 'Плановый контроль',
                'comment' => 'Первичная заявка',
            ])
            ->assertRedirect();

        $requestRecord = NdtRequest::query()->where('request_number', 'NR-001')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.ndt-requests.welds.attach', $requestRecord), [
                'weld_id' => $weld->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('ndt_request_items', [
            'ndt_request_id' => $requestRecord->id,
            'weld_id' => $weld->id,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.ndt-requests.status.update', $requestRecord), [
                'status' => NdtRequestStatus::InWork->value,
                'comment' => 'Передана в работу',
            ])
            ->assertRedirect();

        $requestRecord->refresh();
        $this->assertSame(NdtRequestStatus::InWork, $requestRecord->status);
        $this->assertDatabaseHas('ndt_request_status_history', [
            'ndt_request_id' => $requestRecord->id,
            'to_status' => NdtRequestStatus::InWork->value,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'subject_type' => NdtRequest::class,
            'subject_id' => $requestRecord->id,
            'event' => 'ndt_request.status_updated',
        ]);
    }

    public function test_chief_sees_only_own_object_and_cannot_manage_other_object_records(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $chief = User::factory()->create([
            'name' => 'Начальник участка',
            'email' => 'chief-stage5@example.test',
            'password' => 'password',
            'status' => UserStatus::Active,
        ]);
        $chief->assignRole(Role::findByName('Начальник участка', 'web'));

        $city = City::query()->create([
            'name' => 'Пермь',
            'is_active' => true,
            'comment' => null,
        ]);
        $objectA = NdtObject::query()->create([
            'city_id' => $city->id,
            'name' => 'Участок А',
            'code' => 'A',
            'is_active' => true,
            'comment' => null,
        ]);
        $objectB = NdtObject::query()->create([
            'city_id' => $city->id,
            'name' => 'Участок Б',
            'code' => 'B',
            'is_active' => true,
            'comment' => null,
        ]);
        $position = Position::query()->create([
            'name' => 'Инженер',
            'is_active' => true,
            'comment' => null,
        ]);

        Employee::query()->create([
            'object_id' => $objectA->id,
            'position_id' => $position->id,
            'last_name' => 'Иванов',
            'first_name' => 'Иван',
            'middle_name' => null,
            'phone' => null,
            'email' => null,
            'status' => EmployeeStatus::Active,
            'personnel_number' => '100',
        ])->users()->sync([$chief->id]);

        $organization = Organization::query()->create([
            'name' => 'ООО Южная',
            'is_active' => true,
            'comment' => null,
        ]);

        $requestA = NdtRequest::query()->create([
            'request_number' => 'A-001',
            'request_date' => '2026-07-09',
            'organization_id' => $organization->id,
            'object_id' => $objectA->id,
            'title_id' => null,
            'priority' => null,
            'due_date' => null,
            'basis' => null,
            'comment' => null,
            'status' => NdtRequestStatus::Draft,
        ]);
        $requestB = NdtRequest::query()->create([
            'request_number' => 'B-001',
            'request_date' => '2026-07-09',
            'organization_id' => $organization->id,
            'object_id' => $objectB->id,
            'title_id' => null,
            'priority' => null,
            'due_date' => null,
            'basis' => null,
            'comment' => null,
            'status' => NdtRequestStatus::Draft,
        ]);

        $this->actingAs($chief)
            ->get(route('admin.ndt-requests.index'))
            ->assertOk()
            ->assertSeeText('A-001')
            ->assertDontSeeText('B-001');

        $this->actingAs($chief)
            ->patch(route('admin.ndt-requests.update', $requestB), [
                'request_number' => 'B-001',
                'request_date' => '2026-07-09',
                'organization_id' => $organization->id,
                'object_id' => $objectB->id,
                'title_id' => null,
                'priority' => null,
                'due_date' => null,
                'basis' => null,
                'comment' => null,
            ])
            ->assertForbidden();

        $this->actingAs($chief)
            ->post(route('admin.ndt-requests.store'), [
                'request_number' => 'B-002',
                'request_date' => '2026-07-09',
                'organization_id' => $organization->id,
                'object_id' => $objectB->id,
                'title_id' => null,
                'priority' => null,
                'due_date' => null,
                'basis' => null,
                'comment' => null,
            ])
            ->assertForbidden();
    }

    public function test_request_cannot_move_to_work_without_welds(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $admin = User::query()->where('email', 'admin@example.test')->firstOrFail();
        $city = City::query()->create([
            'name' => 'Тюмень',
            'is_active' => true,
            'comment' => null,
        ]);
        $object = NdtObject::query()->create([
            'city_id' => $city->id,
            'name' => 'Участок 9',
            'code' => 'U9',
            'is_active' => true,
            'comment' => null,
        ]);
        $organization = Organization::query()->create([
            'name' => 'ООО Безстыковая',
            'is_active' => true,
            'comment' => null,
        ]);
        $requestRecord = NdtRequest::query()->create([
            'request_number' => 'NR-FAIL',
            'request_date' => '2026-07-09',
            'organization_id' => $organization->id,
            'object_id' => $object->id,
            'title_id' => null,
            'priority' => null,
            'due_date' => null,
            'basis' => null,
            'comment' => null,
            'status' => NdtRequestStatus::Draft,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.ndt-requests.status.update', $requestRecord), [
                'status' => NdtRequestStatus::InWork->value,
                'comment' => 'Попытка без стыков',
            ])
            ->assertSessionHasErrors('status');
    }
}
