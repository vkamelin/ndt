<?php

declare(strict_types=1);

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WelcomeController;
use App\Modules\Auth\Http\Controllers\AuthenticatedSessionController;
use App\Modules\Auth\Http\Controllers\ProfileController;
use App\Modules\Auth\Http\Controllers\UserController as AdminUserController;
use Illuminate\Support\Facades\Route;

Route::get('/', WelcomeController::class)->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware(['auth', 'active.user'])->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', DashboardController::class)
        ->middleware('can:dashboard.view')
        ->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'show'])
        ->middleware('can:profile.view')
        ->name('profile.show');
});

Route::middleware(['auth', 'active.user', 'can:users.view'])
    ->prefix('admin/users')
    ->name('admin.users.')
    ->group(function (): void {
        Route::get('/', [AdminUserController::class, 'index'])->name('index');
        Route::patch('{user}/roles', [AdminUserController::class, 'updateRoles'])->name('roles.update');
        Route::patch('{user}/block', [AdminUserController::class, 'block'])->name('block');
        Route::patch('{user}/unblock', [AdminUserController::class, 'unblock'])->name('unblock');
    });
