<?php

declare(strict_types=1);

namespace App\Modules\Audit\Services;

use App\Modules\Audit\DTO\AuditData;
use App\Modules\Audit\Models\AuditLog;

final class AuditService
{
    /**
     * Persist a normalized audit log entry.
     */
    public function record(AuditData $data): AuditLog
    {
        return AuditLog::query()->create([
            'actor_user_id' => $data->actor?->getKey(),
            'subject_type' => $data->entityType,
            'subject_id' => $data->entityId,
            'event' => $data->operation,
            'properties' => [
                'before' => $data->before,
                'after' => $data->after,
            ],
            'reason' => $data->reason,
            'ip_address' => $data->ipAddress,
            'user_agent' => $data->userAgent,
        ]);
    }
}
