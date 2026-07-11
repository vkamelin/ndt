<?php

declare(strict_types=1);

namespace Tests\Feature\Documents;

use App\Models\User;
use App\Modules\Auth\Enums\UserStatus;
use App\Modules\Documents\Enums\DocumentStatus;
use App\Modules\Documents\Models\Document;
use App\Modules\Documents\Models\DocumentType;
use App\Modules\Documents\Models\File;
use App\Modules\Employees\Enums\EmployeeStatus;
use App\Modules\Employees\Models\Employee;
use App\Modules\Employees\Models\Position;
use App\Modules\Objects\Models\City;
use App\Modules\Objects\Models\NdtObject;
use App\Modules\Organizations\Models\Organization;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class DocumentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_document_attach_file_and_download_it(): void
    {
        $this->seed(DatabaseSeeder::class);
        Storage::fake('private');

        $admin = User::query()->where('email', 'admin@example.test')->firstOrFail();
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
        $organization = Organization::query()->create([
            'name' => 'Заказчик',
            'comment' => null,
            'is_active' => true,
        ]);
        $documentType = DocumentType::query()->create([
            'name' => 'Акт',
            'is_active' => true,
            'comment' => null,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.documents.store'), [
                'document_type_id' => $documentType->id,
                'number' => 'DOC-001',
                'document_date' => '2026-07-11',
                'organization_id' => $organization->id,
                'city_id' => $city->id,
                'object_id' => $object->id,
                'employee_id' => null,
                'equipment_id' => null,
                'ndt_request_id' => null,
                'valid_until' => '2026-12-31',
                'status' => DocumentStatus::Draft->value,
                'comment' => 'Первичный документ',
            ])
            ->assertRedirect();

        $document = Document::query()->where('number', 'DOC-001')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.files.store'), [
                'document_id' => $document->id,
                'file' => UploadedFile::fake()->createWithContent('act.pdf', 'test-file-content'),
            ])
            ->assertRedirect();

        $file = File::query()->firstOrFail();

        Storage::disk('private')->assertExists($file->storage_path);

        $this->actingAs($admin)
            ->get(route('admin.files.download', $file))
            ->assertOk()
            ->assertDownload('act.pdf');

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'status' => DocumentStatus::Draft->value,
        ]);
        $this->assertDatabaseHas('document_files', [
            'document_id' => $document->id,
            'file_id' => $file->id,
        ]);
    }

    public function test_chief_document_form_uses_scoped_city_and_object(): void
    {
        $this->seed(DatabaseSeeder::class);

        $chief = User::factory()->create([
            'name' => 'Начальник участка',
            'email' => 'chief-document@example.test',
            'password' => 'password',
            'status' => UserStatus::Active,
        ]);
        $chief->assignRole(Role::findByName('Начальник участка', 'web'));

        $city = City::query()->create([
            'name' => 'Пермь',
            'is_active' => true,
            'comment' => null,
        ]);
        $otherCity = City::query()->create([
            'name' => 'Казань',
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
            'personnel_number' => '201',
        ]);
        $employee->users()->sync([$chief->id]);

        $documentType = DocumentType::query()->create([
            'name' => 'Акт',
            'is_active' => true,
            'comment' => null,
        ]);

        $this->actingAs($chief)
            ->post(route('admin.documents.store'), [
                'document_type_id' => $documentType->id,
                'number' => 'DOC-004',
                'document_date' => '2026-07-11',
                'organization_id' => null,
                'city_id' => $otherCity->id,
                'object_id' => $otherObject->id,
                'employee_id' => $employee->id,
                'equipment_id' => null,
                'ndt_request_id' => null,
                'valid_until' => '2026-12-31',
                'status' => DocumentStatus::Draft->value,
                'comment' => 'Подмена контекста',
            ])
            ->assertRedirect();

        $document = Document::query()->where('number', 'DOC-004')->firstOrFail();

        $this->assertSame($object->id, $document->object_id);
        $this->assertSame($city->id, $document->city_id);
        $this->assertSame($employee->id, $document->employee_id);
    }

    public function test_user_from_other_object_cannot_download_document_file(): void
    {
        $this->seed(DatabaseSeeder::class);
        Storage::fake('private');

        $admin = User::query()->where('email', 'admin@example.test')->firstOrFail();
        $chief = User::factory()->create([
            'name' => 'Начальник участка',
            'email' => 'chief-doc@example.test',
            'password' => 'password',
            'status' => UserStatus::Active,
        ]);
        $chief->assignRole(Role::findByName('Дефектоскопист', 'web'));

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
        $documentType = DocumentType::query()->create([
            'name' => 'Сопроводительный',
            'is_active' => true,
            'comment' => null,
        ]);

        $document = Document::query()->create([
            'document_type_id' => $documentType->id,
            'number' => 'DOC-002',
            'document_date' => '2026-07-11',
            'organization_id' => null,
            'city_id' => $city->id,
            'object_id' => $objectA->id,
            'employee_id' => null,
            'equipment_id' => null,
            'ndt_request_id' => null,
            'valid_until' => null,
            'status' => DocumentStatus::Active,
            'comment' => null,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.files.store'), [
                'document_id' => $document->id,
                'file' => UploadedFile::fake()->createWithContent('secret.pdf', 'secret-content'),
            ])
            ->assertRedirect();

        $file = File::query()->firstOrFail();

        $chiefEmployee = Employee::query()->create([
            'object_id' => $objectB->id,
            'position_id' => Position::query()->create([
                'name' => 'Дефектоскопист',
                'is_active' => true,
                'comment' => null,
            ])->id,
            'last_name' => 'Петров',
            'first_name' => 'Петр',
            'middle_name' => null,
            'phone' => null,
            'email' => null,
            'status' => EmployeeStatus::Active,
            'personnel_number' => '200',
        ]);
        $chiefEmployee->users()->sync([$chief->id]);

        $this->actingAs($chief)
            ->get(route('admin.files.download', $file))
            ->assertForbidden();
    }

    public function test_file_deletion_marks_file_as_deleted_and_writes_audit(): void
    {
        $this->seed(DatabaseSeeder::class);
        Storage::fake('private');

        $admin = User::query()->where('email', 'admin@example.test')->firstOrFail();
        $documentType = DocumentType::query()->create([
            'name' => 'Акт',
            'is_active' => true,
            'comment' => null,
        ]);
        $document = Document::query()->create([
            'document_type_id' => $documentType->id,
            'number' => 'DOC-003',
            'document_date' => '2026-07-11',
            'organization_id' => null,
            'city_id' => null,
            'object_id' => null,
            'employee_id' => null,
            'equipment_id' => null,
            'ndt_request_id' => null,
            'valid_until' => null,
            'status' => DocumentStatus::Active,
            'comment' => null,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.files.store'), [
                'document_id' => $document->id,
                'file' => UploadedFile::fake()->createWithContent('delete.pdf', 'delete-me'),
            ])
            ->assertRedirect();

        $file = File::query()->firstOrFail();

        $this->actingAs($admin)
            ->delete(route('admin.files.destroy', $file), [
                'reason' => 'Ошибочная загрузка',
            ])
            ->assertRedirect();

        $this->assertSoftDeleted('files', ['id' => $file->id]);
        $this->assertDatabaseHas('files', [
            'id' => $file->id,
            'status' => 'deleted',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'subject_type' => File::class,
            'subject_id' => $file->id,
            'event' => 'file.deleted',
        ]);
    }
}
