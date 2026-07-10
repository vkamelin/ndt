<?php

declare(strict_types=1);

return [
    'strict_qualification_guard' => (bool) env('EQUIPMENT_STRICT_QUALIFICATION_GUARD', false),

    'warning_days' => [
        'verification' => (int) env('EQUIPMENT_VERIFICATION_WARNING_DAYS', 30),
        'calibration' => (int) env('EQUIPMENT_CALIBRATION_WARNING_DAYS', 30),
        'qualification' => (int) env('EQUIPMENT_QUALIFICATION_WARNING_DAYS', 30),
    ],
];
