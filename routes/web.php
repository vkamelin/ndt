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
use App\Modules\Equipment\Http\Controllers\EquipmentController;
use App\Modules\Objects\Http\Controllers\CityController;
use App\Modules\Objects\Http\Controllers\ObjectController;
use App\Modules\Organizations\Http\Controllers\OrganizationController;
use App\Modules\NdtTasks\Http\Controllers\NdtTaskController;
use App\Modules\NdtResults\Http\Controllers\NdtResultController;
use App\Modules\Documents\Http\Controllers\DocumentController;
use App\Modules\Documents\Http\Controllers\FileController;
use App\Modules\Welds\Http\Controllers\WeldController;
use App\Modules\NdtRequests\Http\Controllers\NdtRequestController;
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
    ->prefix('admin/organizations')
    ->name('admin.organizations.')
    ->scopeBindings()
    ->group(function (): void {
        Route::get('/', [OrganizationController::class, 'index'])->name('index');
        Route::post('/', [OrganizationController::class, 'store'])->name('store');
        Route::get('{organization}', [OrganizationController::class, 'show'])->name('show');
        Route::patch('{organization}', [OrganizationController::class, 'update'])->name('update');
        Route::delete('{organization}', [OrganizationController::class, 'destroy'])->name('destroy');
        Route::post('{organization}/contacts', [OrganizationController::class, 'storeContact'])->name('contacts.store');
        Route::patch('{organization}/contacts/{contact}', [OrganizationController::class, 'updateContact'])->name('contacts.update');
        Route::delete('{organization}/contacts/{contact}', [OrganizationController::class, 'destroyContact'])->name('contacts.destroy');
        Route::post('{organization}/laboratories', [OrganizationController::class, 'storeLaboratory'])->name('laboratories.store');
        Route::patch('{organization}/laboratories/{laboratory}', [OrganizationController::class, 'updateLaboratory'])->name('laboratories.update');
        Route::delete('{organization}/laboratories/{laboratory}', [OrganizationController::class, 'destroyLaboratory'])->name('laboratories.destroy');
    });

Route::middleware(['auth', 'active.user'])
    ->prefix('admin/welds')
    ->name('admin.welds.')
    ->group(function (): void {
        Route::get('/', [WeldController::class, 'index'])->name('index');
        Route::post('/', [WeldController::class, 'store'])->name('store');
        Route::get('{weld}', [WeldController::class, 'show'])->name('show');
        Route::patch('{weld}', [WeldController::class, 'update'])->name('update');
        Route::patch('{weld}/status', [WeldController::class, 'updateStatus'])->name('status.update');
        Route::patch('{weld}/methods', [WeldController::class, 'syncMethods'])->name('methods.sync');
    });

Route::middleware(['auth', 'active.user'])
    ->prefix('admin/ndt-requests')
    ->name('admin.ndt-requests.')
    ->group(function (): void {
        Route::get('/', [NdtRequestController::class, 'index'])->name('index');
        Route::post('/', [NdtRequestController::class, 'store'])->name('store');
        Route::get('{ndtRequest}', [NdtRequestController::class, 'show'])->name('show');
        Route::patch('{ndtRequest}', [NdtRequestController::class, 'update'])->name('update');
        Route::patch('{ndtRequest}/status', [NdtRequestController::class, 'updateStatus'])->name('status.update');
        Route::post('{ndtRequest}/welds', [NdtRequestController::class, 'attachWeld'])->name('welds.attach');
        Route::delete('{ndtRequest}/welds/{weld}', [NdtRequestController::class, 'detachWeld'])->name('welds.detach');
    });

Route::middleware(['auth', 'active.user'])
    ->prefix('admin/ndt-tasks')
    ->name('admin.ndt-tasks.')
    ->group(function (): void {
        Route::get('/', [NdtTaskController::class, 'index'])->name('index');
        Route::post('/', [NdtTaskController::class, 'store'])->name('store');
        Route::get('{ndtTask}', [NdtTaskController::class, 'show'])->name('show');
        Route::patch('{ndtTask}', [NdtTaskController::class, 'update'])->name('update');
        Route::patch('{ndtTask}/accept', [NdtTaskController::class, 'accept'])->name('status.accept');
        Route::patch('{ndtTask}/start-work', [NdtTaskController::class, 'startWork'])->name('status.start');
        Route::patch('{ndtTask}/complete', [NdtTaskController::class, 'complete'])->name('status.complete');
        Route::patch('{ndtTask}/partial', [NdtTaskController::class, 'completePartial'])->name('status.partial');
        Route::patch('{ndtTask}/return', [NdtTaskController::class, 'returnTask'])->name('status.return');
        Route::patch('{ndtTask}/cancel', [NdtTaskController::class, 'cancel'])->name('status.cancel');
    });

Route::middleware(['auth', 'active.user'])
    ->prefix('admin/ndt-results')
    ->name('admin.ndt-results.')
    ->group(function (): void {
        Route::get('/', [NdtResultController::class, 'index'])->name('index');
        Route::post('/', [NdtResultController::class, 'store'])->name('store');
        Route::get('{ndtResult}', [NdtResultController::class, 'show'])->name('show');
        Route::patch('{ndtResult}', [NdtResultController::class, 'update'])->name('update');
        Route::patch('{ndtResult}/analysis', [NdtResultController::class, 'sendToAnalysis'])->name('status.analysis');
        Route::patch('{ndtResult}/defect', [NdtResultController::class, 'markDefect'])->name('status.defect');
        Route::patch('{ndtResult}/ready', [NdtResultController::class, 'markReadyForConclusion'])->name('status.ready');
        Route::patch('{ndtResult}/return', [NdtResultController::class, 'returnForCorrection'])->name('status.return');
        Route::patch('{ndtResult}/approve', [NdtResultController::class, 'approve'])->name('status.approve');
        Route::post('{ndtResult}/defects', [NdtResultController::class, 'storeDefect'])->name('defects.store');
        Route::patch('{ndtResult}/vt', [NdtResultController::class, 'updateVisualControl'])->name('vt.update');
        Route::patch('{ndtResult}/pt', [NdtResultController::class, 'updatePenetrantControl'])->name('pt.update');
        Route::patch('{ndtResult}/mt', [NdtResultController::class, 'updateMagneticControl'])->name('mt.update');
        Route::patch('{ndtResult}/ut', [NdtResultController::class, 'updateUltrasonicControl'])->name('ut.update');
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
    ->prefix('admin/equipment')
    ->name('admin.equipment.')
    ->scopeBindings()
    ->group(function (): void {
        Route::get('/', [EquipmentController::class, 'index'])->name('index');
        Route::post('/', [EquipmentController::class, 'store'])->name('store');
        Route::get('{equipment}', [EquipmentController::class, 'show'])->name('show');
        Route::patch('{equipment}', [EquipmentController::class, 'update'])->name('update');
        Route::delete('{equipment}', [EquipmentController::class, 'destroy'])->name('destroy');
        Route::post('{equipment}/verifications', [EquipmentController::class, 'storeVerification'])->name('verifications.store');
        Route::post('{equipment}/calibrations', [EquipmentController::class, 'storeCalibration'])->name('calibrations.store');
        Route::post('{equipment}/repairs', [EquipmentController::class, 'storeRepair'])->name('repairs.store');
        Route::post('{equipment}/assignments', [EquipmentController::class, 'storeAssignment'])->name('assignments.store');
        Route::patch('{equipment}/assignments/{assignment}/return', [EquipmentController::class, 'returnAssignment'])->name('assignments.return');
        Route::post('{equipment}/movements', [EquipmentController::class, 'storeMovement'])->name('movements.store');
        Route::post('{equipment}/defects', [EquipmentController::class, 'storeDefect'])->name('defects.store');
        Route::post('{equipment}/documents', [EquipmentController::class, 'storeDocument'])->name('documents.store');
    });

Route::middleware(['auth', 'active.user'])
    ->prefix('admin/documents')
    ->name('admin.documents.')
    ->scopeBindings()
    ->group(function (): void {
        Route::get('/', [DocumentController::class, 'index'])->name('index');
        Route::post('/', [DocumentController::class, 'store'])->name('store');
        Route::get('{document}', [DocumentController::class, 'show'])->name('show');
        Route::patch('{document}', [DocumentController::class, 'update'])->name('update');
        Route::post('{document}/versions', [DocumentController::class, 'storeVersion'])->name('versions.store');
    });

Route::middleware(['auth', 'active.user'])
    ->prefix('admin/files')
    ->name('admin.files.')
    ->scopeBindings()
    ->group(function (): void {
        Route::post('/', [FileController::class, 'store'])->name('store');
        Route::get('{file}/download', [FileController::class, 'download'])->name('download');
        Route::delete('{file}', [FileController::class, 'destroy'])->name('destroy');
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
