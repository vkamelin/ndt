<?php

declare(strict_types=1);

namespace Tests\Feature\Audit;

use App\Models\User;
use App\Modules\Audit\DTO\AuditData;
use App\Modules\Audit\Services\AuditService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AuditLogServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_service_records_expected_payload(): void
    {
        $this->seed(DatabaseSeeder::class);

        $actor = User::query()->where('email', 'admin@example.test')->firstOrFail();

        app(AuditService::class)->record(AuditData::forModelChange(
            entityType: User::class,
            entityId: $actor->id,
            operation: 'user.profile.updated',
            before: ['name' => 'Old name'],
            after: ['name' => 'New name'],
            actor: $actor,
            reason: 'Смена ФИО после уточнения данных',
            ipAddress: '127.0.0.1',
            userAgent: 'PHPUnit',
        ));

        $this->assertDatabaseHas('audit_logs', [
            'actor_user_id' => $actor->id,
            'subject_type' => User::class,
            'subject_id' => $actor->id,
            'event' => 'user.profile.updated',
            'reason' => 'Смена ФИО после уточнения данных',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
        ]);
    }
}
