<?php

declare(strict_types=1);

return [
    'health_check' => [
        'storage_disk' => env('HEALTH_CHECK_STORAGE_DISK', env('FILESYSTEM_DISK', 'private')),
        'redis_connection' => env('HEALTH_CHECK_REDIS_CONNECTION', 'default'),
    ],

    'cleanup' => [
        'temporary_file_prefixes' => [
            'act_xlsx_',
            'register_xlsx_',
            'report_xlsx_',
        ],
        'default_hours' => (int) env('TEMPORARY_FILE_CLEANUP_HOURS', 24),
    ],
];
