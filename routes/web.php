<?php

declare(strict_types=1);

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WelcomeController;
use App\Modules\Admin\Http\Controllers\ReferenceDictionaryController;
use App\Modules\Audit\Http\Controllers\AuditLogController;
use App\Modules\Auth\Http\Controllers\AuthenticatedSessionController;
use App\Modules\Auth\Http\Controllers\ProfileController;
use App\Modules\Auth\Http\Controllers\UserController as AdminUserController;
use App\Modules\Employees\Http\Controllers\EmployeeController;
use App\Modules\Employees\Http\Controllers\PositionController;
use App\Modules\Objects\Http\Controllers\CityController;
use App\Modules\Objects\Http\Controllers\ObjectController;
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

Route::middleware(['auth', 'active.user'])
    ->prefix('admin/audit-logs')
    ->name('admin.audit-logs.')
    ->group(function (): void {
        Route::get('/', [AuditLogController::class, 'index'])->name('index');
    });

Route::middleware(['auth', 'active.user'])
    ->prefix('admin/cities')
    ->name('admin.cities.')
    ->group(function (): void {
        Route::get('/', [CityController::class, 'index'])->name('index');
        Route::post('/', [CityController::class, 'store'])->name('store');
        Route::patch('{city}', [CityController::class, 'update'])->name('update');
        Route::delete('{city}', [CityController::class, 'destroy'])->name('destroy');
    });

Route::middleware(['auth', 'active.user'])
    ->prefix('admin/objects')
    ->name('admin.objects.')
    ->group(function (): void {
        Route::get('/', [ObjectController::class, 'index'])->name('index');
        Route::post('/', [ObjectController::class, 'store'])->name('store');
        Route::patch('{object}', [ObjectController::class, 'update'])->name('update');
        Route::delete('{object}', [ObjectController::class, 'destroy'])->name('destroy');
    });

Route::middleware(['auth', 'active.user'])
    ->prefix('admin/positions')
    ->name('admin.positions.')
    ->group(function (): void {
        Route::get('/', [PositionController::class, 'index'])->name('index');
        Route::post('/', [PositionController::class, 'store'])->name('store');
        Route::patch('{position}', [PositionController::class, 'update'])->name('update');
        Route::delete('{position}', [PositionController::class, 'destroy'])->name('destroy');
    });

Route::middleware(['auth', 'active.user'])
    ->prefix('admin/employees')
    ->name('admin.employees.')
    ->group(function (): void {
        Route::get('/', [EmployeeController::class, 'index'])->name('index');
        Route::post('/', [EmployeeController::class, 'store'])->name('store');
        Route::get('{employee}', [EmployeeController::class, 'show'])->name('show');
        Route::patch('{employee}', [EmployeeController::class, 'update'])->name('update');
        Route::delete('{employee}', [EmployeeController::class, 'destroy'])->name('destroy');
        Route::post('{employee}/qualifications', [EmployeeController::class, 'storeQualification'])->name('qualifications.store');
        Route::patch('{employee}/qualifications/{qualification}', [EmployeeController::class, 'updateQualification'])->name('qualifications.update');
        Route::delete('{employee}/qualifications/{qualification}', [EmployeeController::class, 'destroyQualification'])->name('qualifications.destroy');
    });

Route::middleware(['auth', 'active.user'])
    ->prefix('admin/dictionaries')
    ->name('admin.dictionaries.')
    ->group(function (): void {
        Route::get('/', [ReferenceDictionaryController::class, 'overview'])->name('overview');
        Route::get('{dictionary}', [ReferenceDictionaryController::class, 'index'])->name('index');
        Route::post('{dictionary}', [ReferenceDictionaryController::class, 'store'])->name('store');
        Route::patch('{dictionary}/{entry}', [ReferenceDictionaryController::class, 'update'])->name('update');
        Route::delete('{dictionary}/{entry}', [ReferenceDictionaryController::class, 'destroy'])->name('destroy');
    });
