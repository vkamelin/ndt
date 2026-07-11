<?php

declare(strict_types=1);

namespace Tests\Feature\System;

use Illuminate\Contracts\Database\Connection as DatabaseConnection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

final class HealthCheckTest extends TestCase
{
    public function test_health_endpoint_reports_ok_when_dependencies_are_available(): void
    {
        Storage::fake('private');

        $databaseConnection = Mockery::mock(DatabaseConnection::class);
        $databaseConnection->shouldReceive('select')->once()->with('select 1')->andReturn([['1' => 1]]);

        DB::shouldReceive('connection')->once()->andReturn($databaseConnection);

        $redisConnection = Mockery::mock(\Illuminate\Redis\Connections\Connection::class);
        $redisConnection->shouldReceive('ping')->once()->andReturn('PONG');

        Redis::shouldReceive('connection')->once()->with('default')->andReturn($redisConnection);

        $response = $this->getJson('/health');

        $response->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('checks.database.status', 'ok')
            ->assertJsonPath('checks.redis.status', 'ok')
            ->assertJsonPath('checks.storage.status', 'ok');
    }

    public function test_health_endpoint_returns_degraded_when_storage_fails(): void
    {
        Storage::fake('private');

        config(['system.health_check.storage_disk' => 'missing-disk']);

        $databaseConnection = Mockery::mock(DatabaseConnection::class);
        $databaseConnection->shouldReceive('select')->once()->with('select 1')->andReturn([['1' => 1]]);

        DB::shouldReceive('connection')->once()->andReturn($databaseConnection);

        $redisConnection = Mockery::mock(\Illuminate\Redis\Connections\Connection::class);
        $redisConnection->shouldReceive('ping')->once()->andReturn('PONG');

        Redis::shouldReceive('connection')->once()->with('default')->andReturn($redisConnection);

        $response = $this->getJson('/health');

        $response->assertStatus(503)
            ->assertJsonPath('status', 'degraded')
            ->assertJsonPath('checks.storage.status', 'failed');
    }
}
