<?php

declare(strict_types=1);

namespace App\Modules\NdtTasks\Services;

use App\Modules\NdtTasks\Models\NdtTask;
use App\Modules\Notifications\Services\NotificationService;

final class NdtTaskNotificationService
{
    public function __construct(private readonly NotificationService $notifications) {}

    public function notifyAssigned(NdtTask $task): void
    {
        $this->notifications->notifyTaskAssigned($task);
    }
}
