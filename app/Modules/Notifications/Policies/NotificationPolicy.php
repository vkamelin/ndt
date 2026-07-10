<?php

declare(strict_types=1);

namespace App\Modules\Notifications\Policies;

use App\Models\User;
use App\Modules\Notifications\Models\Notification;

final class NotificationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('notifications.view_any') || $user->can('notifications.view_own');
    }

    public function view(User $user, Notification $notification): bool
    {
        return $user->can('notifications.view_any') || (int) $notification->user_id === (int) $user->getKey();
    }

    public function update(User $user, Notification $notification): bool
    {
        return $this->view($user, $notification);
    }

    public function markAll(User $user): bool
    {
        return $user->can('notifications.view_any') || $user->can('notifications.view_own');
    }
}
