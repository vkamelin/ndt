<?php

declare(strict_types=1);

namespace App\Modules\Documents\Policies;

use App\Models\User;
use App\Modules\Documents\Models\Document;

final class DocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('document.view_any');
    }

    public function view(User $user, Document $document): bool
    {
        if ($user->can('document.manage')) {
            if ($user->hasRole('Администратор системы')) {
                return true;
            }

            return $document->object_id === null || $user->objectId() === $document->object_id;
        }

        if (! $user->can('document.view_any')) {
            return false;
        }

        if ($user->hasRole('Администратор системы')) {
            return true;
        }

        return $document->object_id === null || $user->objectId() === $document->object_id;
    }

    public function manage(User $user, Document $document): bool
    {
        if (! $user->can('document.manage')) {
            return false;
        }

        if ($user->hasRole('Администратор системы')) {
            return true;
        }

        return $document->object_id === null || $user->objectId() === $document->object_id;
    }
}
