<?php

declare(strict_types=1);

use Illuminate\Support\Str;

return [
    'default' => env('CACHE_STORE', 'file'),

    'stores' => [
        'array' => [
            'driver' => 'array',
            'serialize' => false,
        ],

        'database' => [
            'driver' => 'database',
            'connection' => env('CACHE_DB_CONNECTION'),
            'table' => env('CACHE_TABLE', 'cache'),
            'lock_connection' => env('CACHE_LOCK_CONNECTION'),
            'lock_table' => env('CACHE_LOCK_TABLE', 'cache_locks'),
        ],

        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache/data'),
            'lock_path' => storage_path('framework/cache/data'),
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => env('CACHE_CONNECTION', 'cache'),
            'lock_connection' => env('CACHE_LOCK_CONNECTION', 'default'),
        ],
    ],

    'prefix' => env('CACHE_PREFIX', Str::slug((string) env('APP_NAME', 'laravel'), '_').'_cache_'),
];
