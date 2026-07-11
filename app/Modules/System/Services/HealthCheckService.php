<?php

declare(strict_types=1);

namespace App\Modules\System\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class HealthCheckService
{
    /**
     * Run a minimal production health-check without exposing secrets.
     *
     * @return array{
     *     status: 'ok'|'degraded',
     *     checked_at: string,
     *     checks: array{
     *         database: array{status: 'ok'|'failed', message?: string},
     *         redis: array{status: 'ok'|'failed', message?: string},
     *         storage: array{status: 'ok'|'failed', message?: string}
     *     }
     * }
     */
    public function check(): array
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'redis' => $this->checkRedis(),
            'storage' => $this->checkStorage(),
        ];

        $overallStatus = $this->allChecksPassed($checks) ? 'ok' : 'degraded';

        return [
            'status' => $overallStatus,
            'checked_at' => now()->toAtomString(),
            'checks' => $checks,
        ];
    }

    /**
     * @return array{status: 'ok'|'failed', message?: string}
     */
    private function checkDatabase(): array
    {
        try {
            DB::connection()->select('select 1');
        } catch (Throwable) {
            return [
                'status' => 'failed',
                'message' => 'База данных недоступна.',
            ];
        }

        return [
            'status' => 'ok',
        ];
    }

    /**
     * @return array{status: 'ok'|'failed', message?: string}
     */
    private function checkRedis(): array
    {
        $connectionName = (string) config('system.health_check.redis_connection', 'default');

        try {
            $connection = Redis::connection($connectionName);
            $connection->ping();
        } catch (Throwable) {
            return [
                'status' => 'failed',
                'message' => 'Redis недоступен.',
            ];
        }

        return [
            'status' => 'ok',
        ];
    }

    /**
     * @return array{status: 'ok'|'failed', message?: string}
     */
    private function checkStorage(): array
    {
        $disk = (string) config('system.health_check.storage_disk', config('filesystems.default', 'private'));

        try {
            Storage::disk($disk)->files('');
        } catch (Throwable) {
            return [
                'status' => 'failed',
                'message' => 'Файловое хранилище недоступно.',
            ];
        }

        return [
            'status' => 'ok',
        ];
    }

    /**
     * @param  array<string, array{status: 'ok'|'failed'}>  $checks
     */
    private function allChecksPassed(array $checks): bool
    {
        foreach ($checks as $check) {
            if ($check['status'] !== 'ok') {
                return false;
            }
        }

        return true;
    }
}
