<?php

declare(strict_types=1);

namespace App\Modules\Audit\Concerns;

use App\Modules\Audit\DTO\AuditData;
use App\Modules\Audit\Models\AuditLog;
use App\Modules\Audit\Services\AuditService;

trait RecordsAuditLogs
{
    /**
     * Store a domain audit record through the shared audit service.
     */
    protected function recordAudit(AuditData $data): AuditLog
    {
        return app(AuditService::class)->record($data);
    }
}
