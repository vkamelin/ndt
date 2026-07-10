<?php

declare(strict_types=1);

namespace App\Modules\Notifications\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Notifications\Models\Notification;
use App\Modules\Notifications\Services\NotificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

final class NotificationController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $showAll = request()->string('scope')->toString() === 'all' && $user?->can('notifications.view_any') === true;

        $query = Notification::query()
            ->with('template')
            ->latest();

        if (! $showAll) {
            $query->where('user_id', $user?->getKey());
        }

        return view('modules.notifications.index', [
            'notifications' => $query->paginate(20),
            'unreadCount' => Notification::query()
                ->when(! $showAll, function ($countQuery) use ($user): void {
                    $countQuery->where('user_id', $user?->getKey());
                })
                ->whereNull('read_at')
                ->count(),
            'showAll' => $showAll,
        ]);
    }

    public function read(Notification $notification, NotificationService $service): RedirectResponse
    {
        $this->authorize('view', $notification);
        $service->markRead($notification, auth()->user());

        return back()->with('status', 'Уведомление отмечено как прочитанное.');
    }

    public function readAll(NotificationService $service): RedirectResponse
    {
        $service->markAllRead(auth()->user());

        return back()->with('status', 'Все уведомления отмечены как прочитанные.');
    }
}
