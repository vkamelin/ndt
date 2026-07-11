<?php

declare(strict_types=1);

namespace App\Modules\Api\Http\Controllers;

use App\Modules\Notifications\Models\Notification;
use App\Modules\Notifications\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class NotificationsController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $showAll = $request->string('scope')->toString() === 'all' && $user?->can('notifications.view_any') === true;

        $query = Notification::query()
            ->with('template')
            ->latest();

        if (! $showAll) {
            $query->where('user_id', $user?->getKey());
        }

        $notifications = $query->paginate(20);

        return $this->paginated($notifications, static function (Notification $notification): array {
            return [
                'id' => $notification->getKey(),
                'type' => $notification->type->value,
                'title' => $notification->title,
                'body' => $notification->body,
                'data' => $notification->data,
                'read_at' => $notification->read_at?->toISOString(),
                'created_at' => $notification->created_at?->toISOString(),
            ];
        });
    }

    public function read(Notification $notification, NotificationService $service): JsonResponse
    {
        $this->authorize('view', $notification);

        return $this->success([
            'notification' => $this->payload($service->markRead($notification, auth()->user())),
        ], 'Уведомление отмечено как прочитанное.');
    }

    public function readAll(NotificationService $service): JsonResponse
    {
        $count = $service->markAllRead(auth()->user());

        return $this->success([
            'updated' => $count,
        ], 'Все уведомления отмечены как прочитанные.');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Notification $notification): array
    {
        return [
            'id' => $notification->getKey(),
            'type' => $notification->type->value,
            'title' => $notification->title,
            'body' => $notification->body,
            'data' => $notification->data,
            'read_at' => $notification->read_at?->toISOString(),
            'created_at' => $notification->created_at?->toISOString(),
        ];
    }
}
