<?php

declare(strict_types=1);

namespace App\Modules\Registers\Policies;

use App\Models\User;
use App\Modules\Registers\Models\ArchiveCase;

final class ArchiveCasePolicy
{
    public function view(User $user, ArchiveCase $archiveCase): bool
    {
        return $user->can('registers.view_any') && $this->scopeMatches($user, $archiveCase);
    }

    public function manage(User $user, ArchiveCase $archiveCase): bool
    {
        return $user->can('registers.archive') && $this->scopeMatches($user, $archiveCase);
    }

    private function scopeMatches(User $user, ArchiveCase $archiveCase): bool
    {
        if ($user->hasRole('Администратор системы')) {
            return true;
        }

        return $archiveCase->object_id !== null && $user->objectId() === $archiveCase->object_id;
    }
}
