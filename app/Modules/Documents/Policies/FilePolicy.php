<?php

declare(strict_types=1);

namespace App\Modules\Documents\Policies;

use App\Models\User;
use App\Modules\Documents\Models\Document;
use App\Modules\Documents\Models\File;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

final class FilePolicy
{
    public function create(User $user, ?Model $related = null): bool
    {
        if ($related === null) {
            return $user->can('file.create');
        }

        if ($related instanceof Document) {
            return $user->can('file.create') && Gate::forUser($user)->allows('manage', $related);
        }

        return $user->can('file.create') && Gate::forUser($user)->allows('manage', $related);
    }

    public function download(User $user, File $file): bool
    {
        if (! $user->can('file.download') || $file->trashed() || $file->status->value === 'deleted') {
            return false;
        }

        $related = $file->related;

        if ($related === null) {
            return false;
        }

        return Gate::forUser($user)->allows('view', $related)
            || Gate::forUser($user)->allows('manage', $related);
    }

    public function delete(User $user, File $file): bool
    {
        if (! $user->can('file.delete') || $file->trashed() || $file->status->value === 'deleted') {
            return false;
        }

        $related = $file->related;

        if ($related === null) {
            return $file->uploaded_by_user_id === $user->getKey();
        }

        return Gate::forUser($user)->allows('manage', $related)
            || $file->uploaded_by_user_id === $user->getKey();
    }
}
