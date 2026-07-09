<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class ProfileController
{
    public function show(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        return view('profile.show', [
            'user' => $user->loadMissing('roles', 'permissions'),
        ]);
    }
}
