<?php

declare(strict_types=1);

namespace Tests\Feature\Registers;

use App\Models\User;
use App\Modules\Admin\Models\ActType;
use App\Modules\Admin\Models\RegisterType;
use App\Modules\Conclusions\Enums\ConclusionStatus;
use App\Modules\Conclusions\Models\Conclusion;
use App\Modules\Documents\Enums\FileStatus;
use App\Modules\Documents\Models\File;
use App\Modules\Employees\Enums\EmployeeStatus;
use App\Modules\Employees\Models\Employee;
use App\Modules\Employees\Models\Position;
use App\Modules\NdtTasks\Models\NdtMethod;
use App\Modules\Objects\Models\City;
use App\Modules\Objects\Models\NdtObject;
use App\Modules\Registers\Enums\TransferRegisterStatus;
use App\Modules\Registers\Models\ArchiveCase;
use App\Modules\Registers\Models\TransferRegister;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class TransferRegisterWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_register_transition_it_and_add_related_documents(): void
    {
        $this->seed(DatabaseSeeder::class);
        Storage::fake('private');

        $admin = User::query()->where('email', 'admin@example.test')->firstOrFail();
        $registerType = RegisterType::query()->orderBy('id')->firstOrFail();
        $actType = ActType::query()->orderBy('id')->firstOrFail();
        $method = NdtMethod::query()->orderBy('id')->firstOrFail();
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
        $sender = Employee::query()->create([
            'object_id' => $object->id,
            'position_id' => $position->id,
            'last_name' => 'Иванов',
            'first_name' => 'Иван',
            'middle_name' => null,
            'phone' => null,
            'email' => null,
            'status' => EmployeeStatus::Active,
            'personnel_number' => '101',
        ]);
        $receiver = Employee::query()->create([
            'object_id' => $object->id,
            'position_id' => $position->id,
            'last_name' => 'Петров',
            'first_name' => 'Петр',
            'middle_name' => null,
            'phone' => null,
            'email' => null,
            'status' => EmployeeStatus::Active,
            'personnel_number' => '102',
        ]);
        $conclusion = Conclusion::query()->create([
            'number' => 'CON-100',
            'date' => '2026-07-11',
            'object_id' => $object->id,
            'ndt_method_id' => $method->id,
            'ndt_request_id' => null,
            'prepared_by_employee_id' => null,
            'checked_by_employee_id' => null,
            'approved_by_employee_id' => null,
            'status' => ConclusionStatus::Draft,
            'comment' => null,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.registers.store'), [
                'register_type_id' => $registerType->id,
                'number' => 'REG-001',
                'date' => '2026-07-11',
                'city_id' => $city->id,
                'object_id' => $object->id,
                'sender_employee_id' => $sender->id,
                'receiver_employee_id' => $receiver->id,
                'status' => TransferRegisterStatus::Draft->value,
                'comment' => 'Первичный реестр',
            ])
            ->assertRedirect();

        $register = TransferRegister::query()->where('number', 'REG-001')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.registers.items.store', $register), [
                'related_type' => Conclusion::class,
                'related_id' => $conclusion->id,
                'sort_order' => 1,
                'comment' => 'Заключение в передаче',
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->post(route('admin.registers.files.store', $register), [
                'file' => UploadedFile::fake()->createWithContent('register.pdf', 'register-content'),
            ])
            ->assertRedirect();

        $register->refresh();
        $file = File::query()->where('related_type', TransferRegister::class)->firstOrFail();
        Storage::disk('private')->assertExists($file->storage_path);
        $this->assertSame(FileStatus::Active, $file->status);

        $this->actingAs($admin)
            ->patch(route('admin.registers.status.update', $register), [
                'status' => TransferRegisterStatus::Formed->value,
                'comment' => 'Сформирован',
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->patch(route('admin.registers.status.update', $register), [
                'status' => TransferRegisterStatus::Sent->value,
                'comment' => 'Передан',
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->patch(route('admin.registers.status.update', $register), [
                'status' => TransferRegisterStatus::Accepted->value,
                'comment' => 'Принят',
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->patch(route('admin.registers.status.update', $register), [
                'status' => TransferRegisterStatus::Closed->value,
                'comment' => 'Закрыт',
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->post(route('admin.registers.acts.store', $register), [
                'act_type_id' => $actType->id,
                'number' => 'ACT-001',
                'date' => '2026-07-11',
                'city_id' => $city->id,
                'object_id' => $object->id,
                'comment' => 'Акт по реестру',
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->post(route('admin.registers.archive-cases.store', $register), [
                'number' => 'ARCH-001',
                'date' => '2026-07-11',
                'city_id' => $city->id,
                'object_id' => $object->id,
                'comment' => 'Архивное дело',
            ])
            ->assertRedirect();

        $archiveCase = ArchiveCase::query()->where('number', 'ARCH-001')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.registers.archive-cases.items.store', $archiveCase), [
                'related_type' => Conclusion::class,
                'related_id' => $conclusion->id,
                'sort_order' => 1,
                'comment' => 'Архивная запись',
            ])
            ->assertRedirect();

        $register->refresh();

        $this->assertSame(TransferRegisterStatus::Closed, $register->status);
        $this->assertDatabaseHas('transfer_register_items', [
            'transfer_register_id' => $register->id,
            'related_type' => Conclusion::class,
            'related_id' => $conclusion->id,
        ]);
        $this->assertDatabaseHas('acts', [
            'transfer_register_id' => $register->id,
            'number' => 'ACT-001',
        ]);
        $this->assertDatabaseHas('archive_cases', [
            'transfer_register_id' => $register->id,
            'number' => 'ARCH-001',
        ]);
        $this->assertDatabaseHas('archive_case_items', [
            'archive_case_id' => $archiveCase->id,
            'related_type' => Conclusion::class,
            'related_id' => $conclusion->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'subject_type' => TransferRegister::class,
            'subject_id' => $register->id,
            'event' => 'transfer_register.closed',
        ]);
    }

    public function test_chief_register_form_uses_scoped_city_and_object(): void
    {
        $this->seed(DatabaseSeeder::class);

        $chief = User::factory()->create([
            'name' => 'Начальник участка',
            'email' => 'chief-register@example.test',
            'password' => 'password',
            'status' => UserStatus::Active,
        ]);
        $chief->assignRole(Role::findByName('Начальник участка', 'web'));

        $city = City::query()->create([
            'name' => 'Самара',
            'is_active' => true,
            'comment' => null,
        ]);
        $otherCity = City::query()->create([
            'name' => 'Уфа',
            'is_active' => true,
            'comment' => null,
        ]);
        $object = NdtObject::query()->create([
            'city_id' => $city->id,
            'name' => 'Участок A',
            'code' => null,
            'is_active' => true,
            'comment' => null,
        ]);
        $otherObject = NdtObject::query()->create([
            'city_id' => $otherCity->id,
            'name' => 'Участок B',
            'code' => null,
            'is_active' => true,
            'comment' => null,
        ]);
        $position = Position::query()->create([
            'name' => 'Лаборант',
            'is_active' => true,
            'comment' => null,
        ]);
        $sender = Employee::query()->create([
            'object_id' => $object->id,
            'position_id' => $position->id,
            'last_name' => 'Иванов',
            'first_name' => 'Иван',
            'middle_name' => null,
            'phone' => null,
            'email' => null,
            'status' => EmployeeStatus::Active,
            'personnel_number' => '103',
        ]);
        $receiver = Employee::query()->create([
            'object_id' => $object->id,
            'position_id' => $position->id,
            'last_name' => 'Петров',
            'first_name' => 'Петр',
            'middle_name' => null,
            'phone' => null,
            'email' => null,
            'status' => EmployeeStatus::Active,
            'personnel_number' => '104',
        ]);
        $registerType = RegisterType::query()->orderBy('id')->firstOrFail();

        $this->actingAs($chief)
            ->post(route('admin.registers.store'), [
                'register_type_id' => $registerType->id,
                'number' => 'REG-002',
                'date' => '2026-07-11',
                'city_id' => $otherCity->id,
                'object_id' => $otherObject->id,
                'sender_employee_id' => $sender->id,
                'receiver_employee_id' => $receiver->id,
                'status' => TransferRegisterStatus::Draft->value,
                'comment' => 'Подмена контекста',
            ])
            ->assertRedirect();

        $register = TransferRegister::query()->where('number', 'REG-002')->firstOrFail();

        $this->assertSame($object->id, $register->object_id);
        $this->assertSame($city->id, $register->city_id);
    }

    public function test_user_from_other_object_cannot_view_register(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::query()->where('email', 'admin@example.test')->firstOrFail();
        $registerType = RegisterType::query()->orderBy('id')->firstOrFail();
        $foreignUser = User::factory()->create([
            'name' => 'Чужой пользователь',
            'email' => 'foreign-register@example.test',
            'password' => 'password',
        ]);
        $foreignUser->assignRole(Role::findByName('Начальник участка', 'web'));

        $city = City::query()->create([
            'name' => 'Пермь',
            'is_active' => true,
            'comment' => null,
        ]);
        $objectA = NdtObject::query()->create([
            'city_id' => $city->id,
            'name' => 'Участок А',
            'code' => null,
            'is_active' => true,
            'comment' => null,
        ]);
        $objectB = NdtObject::query()->create([
            'city_id' => $city->id,
            'name' => 'Участок Б',
            'code' => null,
            'is_active' => true,
            'comment' => null,
        ]);
        $position = Position::query()->create([
            'name' => 'Лаборант',
            'is_active' => true,
            'comment' => null,
        ]);
        $sender = Employee::query()->create([
            'object_id' => $objectA->id,
            'position_id' => $position->id,
            'last_name' => 'Сидоров',
            'first_name' => 'Сидор',
            'middle_name' => null,
            'phone' => null,
            'email' => null,
            'status' => EmployeeStatus::Active,
            'personnel_number' => '201',
        ]);
        $receiver = Employee::query()->create([
            'object_id' => $objectA->id,
            'position_id' => $position->id,
            'last_name' => 'Кузнецов',
            'first_name' => 'Кузьма',
            'middle_name' => null,
            'phone' => null,
            'email' => null,
            'status' => EmployeeStatus::Active,
            'personnel_number' => '202',
        ]);
        $register = TransferRegister::query()->create([
            'register_type_id' => $registerType->id,
            'number' => 'REG-OTHER',
            'date' => '2026-07-11',
            'city_id' => $city->id,
            'object_id' => $objectA->id,
            'sender_employee_id' => $sender->id,
            'receiver_employee_id' => $receiver->id,
            'status' => TransferRegisterStatus::Draft,
            'comment' => null,
        ]);
        $employeeB = Employee::query()->create([
            'object_id' => $objectB->id,
            'position_id' => $position->id,
            'last_name' => 'Ильин',
            'first_name' => 'Илья',
            'middle_name' => null,
            'phone' => null,
            'email' => null,
            'status' => EmployeeStatus::Active,
            'personnel_number' => '203',
        ]);
        $employeeB->users()->sync([$foreignUser->id]);

        $this->actingAs($foreignUser)
            ->get(route('admin.registers.show', $register))
            ->assertForbidden();

        $this->actingAs($foreignUser)
            ->patch(route('admin.registers.status.update', $register), [
                'status' => TransferRegisterStatus::Formed->value,
                'comment' => 'Попытка',
            ])
            ->assertForbidden();
    }
}
