<?php

declare(strict_types=1);

namespace App\Modules\Notifications\Console\Commands;

use App\Modules\Employees\Models\EmployeeQualification;
use App\Modules\Notifications\Services\NotificationService;
use Illuminate\Console\Command;

final class CheckExpiringQualificationsCommand extends Command
{
    protected $signature = 'notifications:check-expiring-qualifications';

    protected $description = 'Send reminders for expiring employee qualifications.';

    public function handle(NotificationService $notifications): int
    {
        $warningDays = (int) config('equipment.warning_days.qualification');

        EmployeeQualification::query()
            ->with(['employee.object', 'employee.users'])
            ->whereNotNull('valid_until')
            ->whereDate('valid_until', '<=', today()->addDays($warningDays))
            ->chunkById(100, function ($qualifications) use ($notifications): void {
                foreach ($qualifications as $qualification) {
                    $notifications->notifyQualificationExpiring($qualification);
                }
            });

        return self::SUCCESS;
    }
}
