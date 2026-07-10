<?php

declare(strict_types=1);

namespace App\Modules\Notifications\Console\Commands;

use App\Modules\NdtTasks\Enums\NdtTaskStatus;
use App\Modules\NdtTasks\Models\NdtTask;
use App\Modules\Notifications\Services\NotificationService;
use Illuminate\Console\Command;

final class CheckOverdueTasksCommand extends Command
{
    protected $signature = 'notifications:check-overdue-tasks';

    protected $description = 'Send notifications for overdue NDT tasks.';

    public function handle(NotificationService $notifications): int
    {
        NdtTask::query()
            ->with(['method', 'object', 'assigneeEmployee.users'])
            ->whereDate('planned_date', '<', today())
            ->whereNotIn('status', [NdtTaskStatus::Completed->value, NdtTaskStatus::Cancelled->value])
            ->chunkById(100, function ($tasks) use ($notifications): void {
                foreach ($tasks as $task) {
                    $notifications->notifyTaskOverdue($task);
                }
            });

        return self::SUCCESS;
    }
}
