<?php

declare(strict_types=1);

namespace App\Modules\Notifications\Jobs;

use App\Modules\Notifications\Enums\NotificationDeliveryStatus;
use App\Modules\Notifications\Models\NotificationDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Throwable;

final class SendNotificationEmailJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly int $deliveryId)
    {
    }

    public function handle(): void
    {
        $delivery = NotificationDelivery::query()
            ->with(['notification.template', 'notification.user'])
            ->findOrFail($this->deliveryId);

        if ($delivery->notification?->user?->email === null || $delivery->notification->user->email === '') {
            $delivery->forceFill([
                'status' => NotificationDeliveryStatus::Failed->value,
                'failed_at' => now(),
                'error_message' => 'Email адрес получателя не задан.',
            ])->save();

            return;
        }

        try {
            Mail::raw($delivery->notification->body, function ($message) use ($delivery): void {
                $message->to($delivery->notification->user->email)
                    ->subject($delivery->notification->template?->subject ?? $delivery->notification->title);
            });

            $delivery->forceFill([
                'status' => NotificationDeliveryStatus::Sent->value,
                'sent_at' => now(),
                'error_message' => null,
            ])->save();
        } catch (Throwable $exception) {
            $delivery->forceFill([
                'status' => NotificationDeliveryStatus::Failed->value,
                'failed_at' => now(),
                'error_message' => $exception->getMessage(),
            ])->save();

            throw $exception;
        }
    }
}
