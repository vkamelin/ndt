<?php

declare(strict_types=1);

namespace App\Modules\Notifications\Enums;

enum NotificationDeliveryStatus: string
{
    case Queued = 'queued';
    case Sent = 'sent';
    case Failed = 'failed';
}
