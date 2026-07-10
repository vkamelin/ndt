<?php

declare(strict_types=1);

namespace App\Modules\NdtTasks\Services;

use App\Modules\NdtTasks\Models\NdtTask;

final class NdtTaskNotificationService
{
    /**
     * Temporary stub for task assignment notifications.
     *
     * Stage 14 will replace this with the real notification pipeline.
     */
    public function notifyAssigned(NdtTask $task): void
    {
        // Intentionally left as a no-op for stage 6.
    }
}
