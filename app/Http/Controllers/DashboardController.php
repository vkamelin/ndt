<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Modules\Employees\Models\EmployeeQualification;
use App\Modules\Equipment\Models\EquipmentCalibration;
use App\Modules\Equipment\Models\EquipmentVerification;
use Illuminate\Contracts\View\View;

final class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $warningDays = config('equipment.warning_days');

        return view('dashboard', [
            'strictQualificationGuard' => (bool) config('equipment.strict_qualification_guard'),
            'expiringVerifications' => EquipmentVerification::query()
                ->with(['equipment.object.city'])
                ->whereNotNull('valid_until')
                ->whereDate('valid_until', '<=', today()->addDays((int) $warningDays['verification']))
                ->orderBy('valid_until')
                ->limit(5)
                ->get(),
            'expiringCalibrations' => EquipmentCalibration::query()
                ->with(['equipment.object.city'])
                ->whereNotNull('valid_until')
                ->whereDate('valid_until', '<=', today()->addDays((int) $warningDays['calibration']))
                ->orderBy('valid_until')
                ->limit(5)
                ->get(),
            'expiringQualifications' => EmployeeQualification::query()
                ->with(['employee.object.city'])
                ->whereNotNull('valid_until')
                ->whereDate('valid_until', '<=', today()->addDays((int) $warningDays['qualification']))
                ->orderBy('valid_until')
                ->limit(5)
                ->get(),
        ]);
    }
}
