<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Auth\Http\Requests\UpdateUserRolesRequest;
use App\Modules\Auth\Services\UserService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

final class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->with('roles')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.users.index', [
            'users' => $users,
        ]);
    }

    public function show(User $user): View
    {
        $user->load(['roles', 'employees.object.city']);

        $roles = Role::query()
            ->orderBy('name')
            ->get();

        return view('admin.users.show', [
            'user' => $user,
            'roles' => $roles,
        ]);
    }

    public function updateRoles(UpdateUserRolesRequest $request, User $user, UserService $users): RedirectResponse
    {
        $this->authorize('assignRoles', $user);

        $users->syncRoles(
            user: $user,
            roleNames: $request->input('roles', []),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Роли пользователя обновлены.');
    }

    public function block(Request $request, User $user, UserService $users): RedirectResponse
    {
        $this->authorize('block', $user);

        $users->block(
            user: $user,
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Пользователь заблокирован.');
    }

    public function unblock(Request $request, User $user, UserService $users): RedirectResponse
    {
        $this->authorize('unblock', $user);

        $users->unblock(
            user: $user,
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Пользователь разблокирован.');
    }
}
