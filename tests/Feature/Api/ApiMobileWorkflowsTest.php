<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use App\Modules\Auth\Enums\UserStatus;
use App\Modules\Employees\Enums\EmployeeStatus;
use App\Modules\Employees\Models\Employee;
use App\Modules\Employees\Models\Position;
use App\Modules\Equipment\Enums\EquipmentStatus;
use App\Modules\Equipment\Models\Equipment;
use App\Modules\Equipment\Models\EquipmentType;
use App\Modules\Objects\Models\City;
use App\Modules\Objects\Models\NdtObject;
use App\Modules\Shifts\Enums\ShiftStatus;
use App\Modules\Shifts\Enums\ShiftType;
use App\Modules\Shifts\Models\Shift;
use App\Modules\Welds\Enums\WeldStatus;
use App\Modules\Welds\Models\Weld;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class ApiMobileWorkflowsTest extends TestCase
{
    use RefreshDatabase;

    public function test_lab_and_decoder_shift_endpoints_work(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $labUser = User::query()->create([
            'name' => 'Лаборант',
            'email' => 'api-lab@example.test',
            'password' => 'password',
            'status' => UserStatus::Active,
        ]);
        $labUser->assignRole(Role::findByName('Лаборант', 'web'));

        $decoderUser = User::query()->create([
            'name' => 'Дешифровщик',
            'email' => 'api-decoder@example.test',
            'password' => 'password',
            'status' => UserStatus::Active,
        ]);
        $decoderUser->assignRole(Role::findByName('Инженер НК / Дешифровщик', 'web'));

        $city = City::query()->create([
            'name' => 'Пермь',
            'is_active' => true,
            'comment' => null,
        ]);
        $object = NdtObject::query()->create([
            'city_id' => $city->id,
            'name' => 'Участок смен',
            'code' => 'SHIFT-PWA',
            'is_active' => true,
            'comment' => null,
        ]);

        $labPosition = Position::query()->create([
            'name' => 'Лаборант',
            'is_active' => true,
            'comment' => null,
        ]);
        $labEmployee = Employee::query()->create([
            'object_id' => $object->id,
            'position_id' => $labPosition->id,
            'last_name' => 'Смирнова',
            'first_name' => 'Анна',
            'middle_name' => null,
            'phone' => null,
            'email' => null,
            'status' => EmployeeStatus::Active,
            'personnel_number' => '401',
        ]);
        $labEmployee->users()->sync([$labUser->id]);

        $decoderPosition = Position::query()->create([
            'name' => 'Инженер НК / Дешифровщик',
            'is_active' => true,
            'comment' => null,
        ]);
        $decoderEmployee = Employee::query()->create([
            'object_id' => $object->id,
            'position_id' => $decoderPosition->id,
            'last_name' => 'Петров',
            'first_name' => 'Петр',
            'middle_name' => null,
            'phone' => null,
            'email' => null,
            'status' => EmployeeStatus::Active,
            'personnel_number' => '402',
        ]);
        $decoderEmployee->users()->sync([$decoderUser->id]);

        $labToken = $this->loginToken('api-lab@example.test');
        $decoderToken = $this->loginToken('api-decoder@example.test');
        $adminUser = User::query()->create([
            'name' => 'Администратор',
            'email' => 'api-admin@example.test',
            'password' => 'password',
            'status' => UserStatus::Active,
        ]);
        $adminUser->assignRole(Role::findByName('Администратор системы', 'web'));
        $adminToken = $this->loginToken('api-admin@example.test');

        $startLab = $this->postJson('/api/mobile/shifts/start', [
            'employee_id' => $labEmployee->id,
            'type' => ShiftType::Lab->value,
            'comment' => 'Лабораторная смена',
        ], [
            'Authorization' => 'Bearer '.$labToken,
        ]);
        $startLab->assertCreated()
            ->assertJsonPath('data.type', ShiftType::Lab->value);

        $labShift = Shift::query()->where('employee_id', $labEmployee->id)->where('type', ShiftType::Lab)->firstOrFail();

        $this->postJson('/api/mobile/shifts/'.$labShift->id.'/lab/regulatory-works', [
            'worked_at' => '2026-07-11 08:15:00',
            'description' => 'Проверка вентиляции',
            'comment' => 'Без замечаний',
        ], [
            'Authorization' => 'Bearer '.$labToken,
        ])->assertOk();

        $this->postJson('/api/mobile/shifts/'.$labShift->id.'/lab/report', [
            'summary' => 'Смена прошла штатно',
            'comment' => 'Отчет',
            'completed_at' => '2026-07-11 16:30:00',
        ], [
            'Authorization' => 'Bearer '.$labToken,
        ])->assertOk();

        $this->postJson('/api/mobile/shifts/'.$labShift->id.'/finish', [
            'comment' => 'Завершение смены',
        ], [
            'Authorization' => 'Bearer '.$labToken,
        ])->assertOk()
            ->assertJsonPath('data.status', ShiftStatus::Completed->value);

        $rtWeld = Weld::query()->create([
            'object_id' => $object->id,
            'title_id' => null,
            'drawing_id' => null,
            'line_id' => null,
            'weld_number' => 'W-2000',
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
            'status' => WeldStatus::Created->value,
        ]);

        $this->getJson('/api/mobile/welds/'.$rtWeld->id, [
            'Authorization' => 'Bearer '.$decoderToken,
        ])->assertOk()
            ->assertJsonPath('data.weld_number', 'W-2000');

        $this->getJson('/api/mobile/welds/search?q=W-2000', [
            'Authorization' => 'Bearer '.$decoderToken,
        ])->assertOk()
            ->assertJsonPath('data.0.weld_number', 'W-2000');

        $equipmentType = EquipmentType::query()->create([
            'name' => 'Дефектоскоп',
            'is_active' => true,
            'comment' => null,
        ]);
        Equipment::query()->create([
            'equipment_type_id' => $equipmentType->id,
            'object_id' => $object->id,
            'name' => 'Установка 1',
            'inventory_number' => 'EQ-1',
            'serial_number' => null,
            'manufacturer' => 'NDT',
            'model' => 'M-1',
            'status' => EquipmentStatus::Available->value,
            'purchased_at' => null,
            'comment' => null,
        ]);

        $this->getJson('/api/mobile/equipment', [
            'Authorization' => 'Bearer '.$labToken,
        ])->assertOk()
            ->assertJsonPath('data.0.inventory_number', 'EQ-1');

        Storage::fake('private');

        $fileResponse = $this->post('/api/mobile/files', [
            'file' => UploadedFile::fake()->create('note.txt', 4, 'text/plain'),
            'related_type' => Weld::class,
            'related_id' => $rtWeld->id,
        ], [
            'Authorization' => 'Bearer '.$adminToken,
        ]);

        $fileResponse->assertCreated()
            ->assertJsonPath('data.original_name', 'note.txt');

        $fileId = (int) $fileResponse->json('data.id');

        $this->get('/api/mobile/files/'.$fileId.'/download', [
            'Authorization' => 'Bearer '.$adminToken,
        ])->assertOk();

        $this->deleteJson('/api/mobile/files/'.$fileId, [], [
            'Authorization' => 'Bearer '.$adminToken,
        ])->assertOk()
            ->assertJsonPath('data.deleted', true);

        $decoderShiftResponse = $this->postJson('/api/mobile/shifts/start', [
            'employee_id' => $decoderEmployee->id,
            'type' => ShiftType::Decoder->value,
            'comment' => 'Дешифровка',
        ], [
            'Authorization' => 'Bearer '.$decoderToken,
        ]);
        $decoderShiftResponse->assertCreated();

        $decoderShift = Shift::query()->where('employee_id', $decoderEmployee->id)->where('type', ShiftType::Decoder)->firstOrFail();

        $this->postJson('/api/mobile/shifts/'.$decoderShift->id.'/decoder/report', [
            'summary' => 'Отчет смены',
            'comment' => 'Отчет',
            'completed_at' => '2026-07-11 18:00:00',
        ], [
            'Authorization' => 'Bearer '.$decoderToken,
        ])->assertOk();

        $this->postJson('/api/mobile/shifts/'.$decoderShift->id.'/decoder/cleanups', [
            'completed_at' => '2026-07-11 18:10:00',
            'comment' => 'Рабочее место очищено',
        ], [
            'Authorization' => 'Bearer '.$decoderToken,
        ])->assertOk();

        $this->postJson('/api/mobile/shifts/'.$decoderShift->id.'/decoder/decryptions', [
            'rt_result_id' => null,
            'result_text' => 'Материал просмотрен',
            'analysis_comment' => 'Замечаний нет',
            'decrypted_at' => '2026-07-11 18:20:00',
        ], [
            'Authorization' => 'Bearer '.$decoderToken,
        ])->assertOk();

        $this->postJson('/api/mobile/shifts/'.$decoderShift->id.'/finish', [
            'comment' => 'Завершение дешифровки',
        ], [
            'Authorization' => 'Bearer '.$decoderToken,
        ])->assertOk()
            ->assertJsonPath('data.status', ShiftStatus::Completed->value);
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
