<?php

declare(strict_types=1);

namespace App\Modules\Notifications\Models;

use App\Modules\Notifications\Enums\NotificationChannel;
use App\Modules\Notifications\Enums\NotificationDeliveryStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class NotificationDelivery extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'notification_id',
        'channel',
        'recipient_address',
        'status',
        'queued_at',
        'sent_at',
        'failed_at',
        'error_message',
        'meta',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'channel' => NotificationChannel::class,
        'status' => NotificationDeliveryStatus::class,
        'queued_at' => 'datetime',
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
        'meta' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class);
    }
}
