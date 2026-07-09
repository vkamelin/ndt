<?php

declare(strict_types=1);

namespace App\Modules\Audit\Policies;

use App\Models\User;
use App\Modules\Audit\Models\AuditLog;

final class AuditLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('audit_log.view_any');
    }

    public function view(User $user, AuditLog $auditLog): bool
    {
        return $this->viewAny($user);
    }
}
