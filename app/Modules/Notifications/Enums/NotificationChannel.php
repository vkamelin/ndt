<?php

declare(strict_types=1);

namespace App\Modules\Notifications\Enums;

enum NotificationChannel: string
{
    case Database = 'database';
    case Email = 'email';
}
