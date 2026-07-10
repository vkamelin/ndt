<?php

declare(strict_types=1);

namespace App\Modules\Notifications\Console\Commands;

use App\Modules\Notifications\Services\NotificationService;
use App\Modules\Shifts\Enums\ShiftStatus;
use App\Modules\Shifts\Models\Shift;
use Illuminate\Console\Command;

final class CheckOpenShiftsCommand extends Command
{
    protected $signature = 'notifications:check-open-shifts';

    protected $description = 'Send reminders for incomplete shifts.';

    public function handle(NotificationService $notifications): int
    {
        Shift::query()
            ->with(['employee.users', 'object'])
            ->whereIn('status', [ShiftStatus::Open->value, ShiftStatus::InProgress->value, ShiftStatus::AwaitingCompletion->value])
            ->whereDate('started_at', '<=', today()->subDay())
            ->chunkById(100, function ($shifts) use ($notifications): void {
                foreach ($shifts as $shift) {
                    $notifications->notifyShiftIncomplete($shift);
                }
            });

        return self::SUCCESS;
    }
}
