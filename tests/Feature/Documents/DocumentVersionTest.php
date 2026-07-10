<?php

declare(strict_types=1);

namespace Tests\Feature\Documents;

use App\Models\User;
use App\Modules\Documents\Enums\DocumentStatus;
use App\Modules\Documents\Enums\DocumentVersionStatus;
use App\Modules\Documents\Models\Document;
use App\Modules\Documents\Models\DocumentType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class DocumentVersionTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_versions_increment_and_previous_versions_become_superseded(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        Storage::fake('private');

        $admin = User::query()->where('email', 'admin@example.test')->firstOrFail();
        $documentType = DocumentType::query()->create([
            'name' => 'Заключение',
            'is_active' => true,
            'comment' => null,
        ]);
        $document = Document::query()->create([
            'document_type_id' => $documentType->id,
            'number' => 'DOC-010',
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
            ->post(route('admin.documents.versions.store', $document), [
                'file' => UploadedFile::fake()->createWithContent('v1.pdf', 'version-one'),
                'basis' => 'Первичная версия',
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->post(route('admin.documents.versions.store', $document), [
                'file' => UploadedFile::fake()->createWithContent('v2.pdf', 'version-two'),
                'basis' => 'Исправленная версия',
            ])
            ->assertRedirect();

        $document->refresh();
        $versions = $document->versions()->orderBy('version_number')->get();

        $this->assertCount(2, $versions);
        $this->assertSame(1, $versions[0]->version_number);
        $this->assertSame(DocumentVersionStatus::Superseded, $versions[0]->status);
        $this->assertSame(2, $versions[1]->version_number);
        $this->assertSame(DocumentVersionStatus::Current, $versions[1]->status);
    }
}
