<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | View Paths
    |--------------------------------------------------------------------------
    |
    | These are the paths that will be searched for Blade views. The default
    | application layout keeps views under resources/views.
    |
    */
    'paths' => [
        resource_path('views'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Compiled View Path
    |--------------------------------------------------------------------------
    |
    | Blade compiles templates into PHP files stored in storage/framework/views.
    | Laravel requires this path to be defined during boot.
    |
    */
    'compiled' => env('VIEW_COMPILED_PATH', storage_path('framework/views')),
];
