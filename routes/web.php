<?php

declare(strict_types=1);

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PwaController;
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
use App\Modules\Conclusions\Http\Controllers\ConclusionController;
use App\Modules\Radiography\Http\Controllers\RadiographyController;
use App\Modules\Registers\Http\Controllers\TransferRegisterController;
use App\Modules\Notifications\Http\Controllers\NotificationController;
use App\Modules\Shifts\Http\Controllers\ShiftController;
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
    Route::get('/notifications', [NotificationController::class, 'index'])
        ->middleware('can:notifications.view_own')
        ->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])
        ->middleware('can:notifications.view_own')
        ->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])
        ->middleware('can:notifications.view_own')
        ->name('notifications.read-all');
    Route::get('/profile', [ProfileController::class, 'show'])
        ->middleware('can:profile.view')
        ->name('profile.show');
});

Route::middleware(['auth', 'active.user'])
    ->prefix('pwa')
    ->name('pwa.')
    ->group(function (): void {
        Route::get('/tasks', [PwaController::class, 'tasks'])->name('tasks');
        Route::get('/lab-shift', [PwaController::class, 'labShift'])->name('lab-shift');
        Route::get('/decoder', [PwaController::class, 'decoder'])->name('decoder');
        Route::get('/control', [PwaController::class, 'control'])->name('control');
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
    ->prefix('admin/conclusions')
    ->name('admin.conclusions.')
    ->scopeBindings()
    ->group(function (): void {
        Route::get('/', [ConclusionController::class, 'index'])->name('index');
        Route::post('/', [ConclusionController::class, 'store'])->name('store');
        Route::get('{conclusion}', [ConclusionController::class, 'show'])->name('show');
        Route::patch('{conclusion}', [ConclusionController::class, 'update'])->name('update');
        Route::post('{conclusion}/submit', [ConclusionController::class, 'submit'])->name('submit');
        Route::patch('{conclusion}/approve', [ConclusionController::class, 'approve'])->name('approve');
        Route::patch('{conclusion}/return', [ConclusionController::class, 'returnForRevision'])->name('return');
        Route::patch('{conclusion}/issue', [ConclusionController::class, 'issue'])->name('issue');
        Route::patch('{conclusion}/annul', [ConclusionController::class, 'annul'])->name('annul');
        Route::post('{conclusion}/replace', [ConclusionController::class, 'replace'])->name('replace');
        Route::post('{conclusion}/versions', [ConclusionController::class, 'storeVersion'])->name('versions.store');
        Route::post('{conclusion}/files', [ConclusionController::class, 'storeFile'])->name('files.store');
    });

Route::middleware(['auth', 'active.user'])
    ->prefix('admin/radiography')
    ->name('admin.radiography.')
    ->scopeBindings()
    ->group(function (): void {
        Route::get('/', [RadiographyController::class, 'index'])->name('index');
        Route::post('/', [RadiographyController::class, 'store'])->name('store');
        Route::get('{rtResult}', [RadiographyController::class, 'show'])->name('show');
        Route::patch('{rtResult}/status', [RadiographyController::class, 'updateStatus'])->name('status.update');
        Route::post('{rtResult}/films', [RadiographyController::class, 'storeFilm'])->name('films.store');
        Route::post('{rtResult}/reshoots', [RadiographyController::class, 'storeReshoot'])->name('reshoots.store');
        Route::post('{rtResult}/densities', [RadiographyController::class, 'storeDensity'])->name('densities.store');
        Route::post('{rtResult}/archive-items', [RadiographyController::class, 'storeArchiveItem'])->name('archive-items.store');
        Route::post('{rtFilm}/images', [RadiographyController::class, 'storeImage'])->name('images.store');
        Route::post('{rtFilm}/exposures', [RadiographyController::class, 'storeExposure'])->name('exposures.store');
    });

Route::middleware(['auth', 'active.user'])
    ->prefix('admin/registers')
    ->name('admin.registers.')
    ->scopeBindings()
    ->group(function (): void {
        Route::get('/', [TransferRegisterController::class, 'index'])->name('index');
        Route::post('/', [TransferRegisterController::class, 'store'])->name('store');
        Route::get('{transferRegister}', [TransferRegisterController::class, 'show'])->name('show');
        Route::patch('{transferRegister}', [TransferRegisterController::class, 'update'])->name('update');
        Route::patch('{transferRegister}/status', [TransferRegisterController::class, 'updateStatus'])->name('status.update');
        Route::post('{transferRegister}/items', [TransferRegisterController::class, 'storeItem'])->name('items.store');
        Route::post('{transferRegister}/files', [TransferRegisterController::class, 'storeFile'])->name('files.store');
        Route::post('{transferRegister}/export/pdf', [TransferRegisterController::class, 'exportPdf'])->name('export.pdf');
        Route::post('{transferRegister}/export/excel', [TransferRegisterController::class, 'exportExcel'])->name('export.excel');
        Route::post('{transferRegister}/acts', [TransferRegisterController::class, 'storeAct'])->name('acts.store');
        Route::post('{act}/export/pdf', [TransferRegisterController::class, 'exportActPdf'])->name('acts.export.pdf');
        Route::post('{act}/export/excel', [TransferRegisterController::class, 'exportActExcel'])->name('acts.export.excel');
        Route::post('{transferRegister}/archive-cases', [TransferRegisterController::class, 'storeArchiveCase'])->name('archive-cases.store');
        Route::post('{archiveCase}/items', [TransferRegisterController::class, 'storeArchiveCaseItem'])->name('archive-cases.items.store');
    });

Route::middleware(['auth', 'active.user'])
    ->prefix('admin/shifts')
    ->name('admin.shifts.')
    ->scopeBindings()
    ->group(function (): void {
        Route::get('/', [ShiftController::class, 'index'])->name('index');
        Route::post('/', [ShiftController::class, 'store'])->name('store');
        Route::get('{shift}', [ShiftController::class, 'show'])->name('show');
        Route::patch('{shift}/complete', [ShiftController::class, 'complete'])->name('complete');
        Route::post('{shift}/lab/report', [ShiftController::class, 'storeLabReport'])->name('lab.reports.store');
        Route::post('{shift}/lab/regulatory-works', [ShiftController::class, 'storeLabRegulatoryWork'])->name('lab.regulatory-works.store');
        Route::post('{shift}/lab/film-transactions', [ShiftController::class, 'storeFilmTransaction'])->name('lab.film-transactions.store');
        Route::post('{shift}/lab/chemical-transactions', [ShiftController::class, 'storeChemicalTransaction'])->name('lab.chemical-transactions.store');
        Route::post('{shift}/lab/chemical-requests', [ShiftController::class, 'storeChemicalRequest'])->name('lab.chemical-requests.store');
        Route::post('{shift}/decoder/report', [ShiftController::class, 'storeDecoderReport'])->name('decoder.reports.store');
        Route::post('{shift}/decoder/film-groups', [ShiftController::class, 'storeDecoderFilmGroup'])->name('decoder.film-groups.store');
        Route::post('{shift}/decoder/rejects', [ShiftController::class, 'storeDecoderReject'])->name('decoder.rejects.store');
        Route::post('{shift}/decoder/forgery-suspicions', [ShiftController::class, 'storeDecoderForgerySuspicion'])->name('decoder.forgery-suspicions.store');
        Route::post('{shift}/decoder/cleanups', [ShiftController::class, 'storeDecoderCleanup'])->name('decoder.cleanups.store');
        Route::post('{shift}/decoder/decryptions', [ShiftController::class, 'storeDecoderDecryption'])->name('decoder.decryptions.store');
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
