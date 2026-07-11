<?php

declare(strict_types=1);

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('app:ping', function (): int {
    $this->comment('Application is ready.');

    return Command::SUCCESS;
})->purpose('Smoke check for the base application scaffold');

Schedule::command('notifications:check-overdue-tasks')->everyTenMinutes();
Schedule::command('notifications:check-open-shifts')->everyThirtyMinutes();
Schedule::command('notifications:check-expiring-equipment')->hourly();
Schedule::command('notifications:check-expiring-qualifications')->dailyAt('07:00');
Schedule::command('notifications:check-queue')->everyFifteenMinutes();
Schedule::command('system:cleanup-temporary-files', ['--hours' => (int) config('system.cleanup.default_hours', 24)])
    ->dailyAt('02:30');
