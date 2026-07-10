<?php

declare(strict_types=1);

namespace App\Modules\Conclusions\Policies;

use App\Models\User;
use App\Modules\Conclusions\Models\Conclusion;

final class ConclusionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('conclusions.view_any');
    }

    public function view(User $user, Conclusion $conclusion): bool
    {
        return $this->scopeMatches($user, $conclusion) && $user->can('conclusions.view_any');
    }

    public function create(User $user): bool
    {
        return $user->can('conclusions.manage');
    }

    public function manage(User $user, Conclusion $conclusion): bool
    {
        return $user->can('conclusions.manage')
            && $this->scopeMatches($user, $conclusion)
            && $conclusion->status->canBeEdited();
    }

    public function version(User $user, Conclusion $conclusion): bool
    {
        return $user->can('conclusions.version')
            && $this->scopeMatches($user, $conclusion)
            && ! in_array($conclusion->status, [\App\Modules\Conclusions\Enums\ConclusionStatus::Annulled, \App\Modules\Conclusions\Enums\ConclusionStatus::Replaced], true);
    }

    public function approve(User $user, Conclusion $conclusion): bool
    {
        return $user->can('conclusions.approve')
            && $this->scopeMatches($user, $conclusion)
            && in_array($conclusion->status, [\App\Modules\Conclusions\Enums\ConclusionStatus::OnCheck, \App\Modules\Conclusions\Enums\ConclusionStatus::Returned], true);
    }

    public function issue(User $user, Conclusion $conclusion): bool
    {
        return $user->can('conclusions.issue')
            && $this->scopeMatches($user, $conclusion)
            && $conclusion->status === \App\Modules\Conclusions\Enums\ConclusionStatus::Approved;
    }

    public function replace(User $user, Conclusion $conclusion): bool
    {
        return $user->can('conclusions.replace')
            && $this->scopeMatches($user, $conclusion)
            && ! in_array($conclusion->status, [\App\Modules\Conclusions\Enums\ConclusionStatus::Annulled, \App\Modules\Conclusions\Enums\ConclusionStatus::Replaced], true);
    }

    public function annul(User $user, Conclusion $conclusion): bool
    {
        return $user->can('conclusions.annul')
            && $this->scopeMatches($user, $conclusion)
            && ! in_array($conclusion->status, [\App\Modules\Conclusions\Enums\ConclusionStatus::Annulled, \App\Modules\Conclusions\Enums\ConclusionStatus::Replaced], true);
    }

    private function scopeMatches(User $user, Conclusion $conclusion): bool
    {
        if ($user->hasRole('Администратор системы')) {
            return true;
        }

        return $user->objectId() === $conclusion->object_id;
    }
}
