<?php

declare(strict_types=1);

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

Artisan::command('app:ping', function (): int {
    $this->comment('Application is ready.');

    return Command::SUCCESS;
})->purpose('Smoke check for the base application scaffold');
