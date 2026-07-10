<?php

declare(strict_types=1);

use App\Modules\Api\Http\Controllers\AuthController;
use App\Modules\Api\Http\Controllers\NotificationsController;
use App\Modules\Api\Http\Controllers\MobileEquipmentController;
use App\Modules\Api\Http\Controllers\MobileFilesController;
use App\Modules\Api\Http\Controllers\MobileShiftsController;
use App\Modules\Api\Http\Controllers\MobileTasksController;
use App\Modules\Api\Http\Controllers\MobileWeldsController;
use App\Modules\Api\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function (): void {
    Route::prefix('auth')->middleware('throttle:api-auth')->group(function (): void {
        Route::post('login', [AuthController::class, 'login'])->name('api.auth.login');
    });

    Route::middleware(['auth:sanctum', 'active.user', 'throttle:api'])->group(function (): void {
        Route::post('auth/logout', [AuthController::class, 'logout'])->name('api.auth.logout');
        Route::get('profile', [ProfileController::class, 'show'])->name('api.profile.show');
        Route::prefix('notifications')->name('api.notifications.')->group(function (): void {
            Route::get('/', [NotificationsController::class, 'index'])->name('index');
            Route::post('{notification}/read', [NotificationsController::class, 'read'])->name('read');
            Route::post('read-all', [NotificationsController::class, 'readAll'])->name('read-all');
        });

        Route::prefix('mobile/tasks')->name('api.mobile.tasks.')->group(function (): void {
            Route::get('/', [MobileTasksController::class, 'index'])->name('index');
            Route::get('{task}', [MobileTasksController::class, 'show'])->name('show');
            Route::post('{task}/accept', [MobileTasksController::class, 'accept'])->name('accept');
            Route::post('{task}/items/{item}/complete', [MobileTasksController::class, 'completeItem'])->name('items.complete');
            Route::post('{task}/items/{item}/files', [MobileTasksController::class, 'uploadItemFile'])->name('items.files.store');
            Route::post('{task}/finish', [MobileTasksController::class, 'finish'])->name('finish');
        });

        Route::prefix('mobile/shifts')->name('api.mobile.shifts.')->group(function (): void {
            Route::get('current', [MobileShiftsController::class, 'current'])->name('current');
            Route::post('start', [MobileShiftsController::class, 'start'])->name('start');
            Route::post('{shift}/finish', [MobileShiftsController::class, 'finish'])->name('finish');
            Route::post('{shift}/lab/report', [MobileShiftsController::class, 'storeLabReport'])->name('lab.report');
            Route::post('{shift}/lab/regulatory-works', [MobileShiftsController::class, 'storeLabRegulatoryWork'])->name('lab.regulatory-works');
            Route::post('{shift}/lab/film-transactions', [MobileShiftsController::class, 'storeFilmTransaction'])->name('lab.film-transactions');
            Route::post('{shift}/lab/chemical-transactions', [MobileShiftsController::class, 'storeChemicalTransaction'])->name('lab.chemical-transactions');
            Route::post('{shift}/lab/chemical-requests', [MobileShiftsController::class, 'storeChemicalRequest'])->name('lab.chemical-requests');
            Route::post('{shift}/decoder/report', [MobileShiftsController::class, 'storeDecoderReport'])->name('decoder.report');
            Route::post('{shift}/decoder/film-groups', [MobileShiftsController::class, 'storeDecoderFilmGroup'])->name('decoder.film-groups');
            Route::post('{shift}/decoder/rejects', [MobileShiftsController::class, 'storeDecoderReject'])->name('decoder.rejects');
            Route::post('{shift}/decoder/forgery-suspicions', [MobileShiftsController::class, 'storeDecoderForgerySuspicion'])->name('decoder.forgery-suspicions');
            Route::post('{shift}/decoder/cleanups', [MobileShiftsController::class, 'storeDecoderCleanup'])->name('decoder.cleanups');
            Route::post('{shift}/decoder/decryptions', [MobileShiftsController::class, 'storeDecoderDecryption'])->name('decoder.decryptions');
        });

        Route::prefix('mobile/welds')->name('api.mobile.welds.')->group(function (): void {
            Route::get('search', [MobileWeldsController::class, 'search'])->name('search');
            Route::get('{weld}', [MobileWeldsController::class, 'show'])->name('show');
            Route::get('{weld}/results', [MobileWeldsController::class, 'results'])->name('results');
        });

        Route::prefix('mobile/files')->name('api.mobile.files.')->group(function (): void {
            Route::post('/', [MobileFilesController::class, 'store'])->name('store');
            Route::get('{file}/download', [MobileFilesController::class, 'download'])->name('download');
            Route::delete('{file}', [MobileFilesController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('mobile/equipment')->name('api.mobile.equipment.')->group(function (): void {
            Route::get('/', [MobileEquipmentController::class, 'index'])->name('index');
        });
    });
});
