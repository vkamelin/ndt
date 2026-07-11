<?php

declare(strict_types=1);

namespace Tests\Feature\Audit;

use App\Models\User;
use App\Modules\Audit\DTO\AuditData;
use App\Modules\Audit\Livewire\AuditLogList;
use App\Modules\Audit\Services\AuditService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class AuditLogListTest extends TestCase
{
    use RefreshDatabase;

    public function test_filters_are_applied_on_the_database_level(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::query()->where('email', 'admin@example.test')->firstOrFail();
        $target = User::factory()->create([
            'email' => 'filtered@example.test',
            'password' => 'password',
        ]);

        app(AuditService::class)->record(AuditData::forModelChange(
            entityType: User::class,
            entityId: $admin->id,
            operation: 'user.blocked',
            before: ['status' => 'active'],
            after: ['status' => 'blocked'],
            actor: $admin,
            ipAddress: '127.0.0.1',
            userAgent: 'PHPUnit',
        ));

        app(AuditService::class)->record(AuditData::forModelChange(
            entityType: User::class,
            entityId: $target->id,
            operation: 'user.roles.updated',
            before: ['roles' => []],
            after: ['roles' => ['Лаборант']],
            actor: $admin,
            ipAddress: '127.0.0.1',
            userAgent: 'PHPUnit',
        ));

        Livewire::test(AuditLogList::class)
            ->set('operation', 'user.roles.updated')
            ->assertViewHas('logs', function ($logs): bool {
                return $logs->total() === 1;
            });
    }
}
