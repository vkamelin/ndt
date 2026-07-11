<?php

declare(strict_types=1);

namespace App\Modules\System\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\System\Services\HealthCheckService;
use Illuminate\Http\JsonResponse;

final class HealthController extends Controller
{
    public function __invoke(HealthCheckService $healthCheckService): JsonResponse
    {
        $result = $healthCheckService->check();

        return response()
            ->json($result, $result['status'] === 'ok' ? 200 : 503)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }
}
