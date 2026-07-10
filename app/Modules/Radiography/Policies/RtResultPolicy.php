<?php

declare(strict_types=1);

namespace App\Modules\Radiography\Policies;

use App\Models\User;
use App\Modules\Radiography\Enums\RtStatus;
use App\Modules\Radiography\Models\RtResult;
use App\Modules\NdtResults\Models\NdtResult;

final class RtResultPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('radiography.view_any');
    }

    public function create(User $user, NdtResult $ndtResult): bool
    {
        return $user->can('radiography.manage') && $this->scopeMatchesByNdtResult($user, $ndtResult);
    }

    public function view(User $user, RtResult $result): bool
    {
        return $this->manage($user, $result) || $this->scopeMatches($user, $result);
    }

    public function manage(User $user, RtResult $result): bool
    {
        return $user->can('radiography.manage') && $this->scopeMatches($user, $result);
    }

    public function transition(User $user, RtResult $result): bool
    {
        return $this->manage($user, $result) && in_array($result->status, [
            RtStatus::Assigned,
            RtStatus::Shot,
            RtStatus::LabTransferred,
            RtStatus::Processing,
            RtStatus::ReadyForDecoding,
            RtStatus::Decoding,
            RtStatus::NeedsReshoot,
            RtStatus::ReshootDone,
            RtStatus::Decoded,
            RtStatus::SentToAnalysis,
            RtStatus::IncludedInConclusion,
        ], true);
    }

    private function scopeMatches(User $user, RtResult $result): bool
    {
        if ($user->hasRole('Администратор системы')) {
            return true;
        }

        $result->loadMissing('ndtResult.weld');

        return $user->objectId() === $result->ndtResult?->weld?->object_id;
    }

    private function scopeMatchesByNdtResult(User $user, NdtResult $ndtResult): bool
    {
        if ($user->hasRole('Администратор системы')) {
            return true;
        }

        $ndtResult->loadMissing('weld');

        return $user->objectId() === $ndtResult->weld?->object_id;
    }
}
