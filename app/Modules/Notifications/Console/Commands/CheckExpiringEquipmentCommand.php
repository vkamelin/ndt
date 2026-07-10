<?php

declare(strict_types=1);

namespace App\Modules\Notifications\Console\Commands;

use App\Modules\Equipment\Models\EquipmentCalibration;
use App\Modules\Equipment\Models\EquipmentVerification;
use App\Modules\Notifications\Services\NotificationService;
use Illuminate\Console\Command;

final class CheckExpiringEquipmentCommand extends Command
{
    protected $signature = 'notifications:check-expiring-equipment';

    protected $description = 'Send reminders for expiring verifications and calibrations.';

    public function handle(NotificationService $notifications): int
    {
        $verificationDays = (int) config('equipment.warning_days.verification');
        $calibrationDays = (int) config('equipment.warning_days.calibration');

        EquipmentVerification::query()
            ->with(['equipment.object'])
            ->whereNotNull('valid_until')
            ->whereDate('valid_until', '<=', today()->addDays($verificationDays))
            ->chunkById(100, function ($verifications) use ($notifications): void {
                foreach ($verifications as $verification) {
                    $notifications->notifyEquipmentVerificationExpiring($verification);
                }
            });

        EquipmentCalibration::query()
            ->with(['equipment.object'])
            ->whereNotNull('valid_until')
            ->whereDate('valid_until', '<=', today()->addDays($calibrationDays))
            ->chunkById(100, function ($calibrations) use ($notifications): void {
                foreach ($calibrations as $calibration) {
                    $notifications->notifyEquipmentCalibrationExpiring($calibration);
                }
            });

        return self::SUCCESS;
    }
}
