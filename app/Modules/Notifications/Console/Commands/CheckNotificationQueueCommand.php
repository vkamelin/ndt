<?php

declare(strict_types=1);

namespace App\Modules\Notifications\Console\Commands;

use App\Modules\Notifications\Enums\NotificationDeliveryStatus;
use App\Modules\Notifications\Models\NotificationDelivery;
use App\Modules\Notifications\Services\NotificationService;
use Illuminate\Console\Command;

final class CheckNotificationQueueCommand extends Command
{
    protected $signature = 'notifications:check-queue';

    protected $description = 'Check notification delivery queue health.';

    public function handle(NotificationService $notifications): int
    {
        $queuedCount = NotificationDelivery::query()
            ->where('status', NotificationDeliveryStatus::Queued->value)
            ->where('created_at', '<', now()->subMinutes(15))
            ->count();

        $failedCount = NotificationDelivery::query()
            ->where('status', NotificationDeliveryStatus::Failed->value)
            ->whereDate('updated_at', today())
            ->count();

        if ($queuedCount > 0 || $failedCount > 0) {
            $notifications->notifyQueueFailure('Обнаружены проблемы с очередью уведомлений.', [
                'queued_count' => $queuedCount,
                'failed_count' => $failedCount,
            ]);
        }

        return self::SUCCESS;
    }
}
