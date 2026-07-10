<?php

declare(strict_types=1);

namespace App\Modules\Reports\Policies;

use App\Models\User;
use App\Modules\Reports\Models\ReportJob;

final class ReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('reports.view_any') || $user->can('reports.manage');
    }

    public function view(User $user, ReportJob $reportJob): bool
    {
        return ($user->can('reports.view_any') || $user->can('reports.manage'))
            && $this->matchesScope($user, $reportJob);
    }

    public function create(User $user): bool
    {
        return $user->can('reports.manage');
    }

    public function manage(User $user, ReportJob $reportJob): bool
    {
        return $user->can('reports.manage') && $this->matchesScope($user, $reportJob);
    }

    private function matchesScope(User $user, ReportJob $reportJob): bool
    {
        if ($user->hasRole('Администратор системы')) {
            return true;
        }

        return $reportJob->object_id !== null && $user->objectId() === $reportJob->object_id;
    }
}
