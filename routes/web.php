<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Config\AssetCategoryController;
use App\Http\Controllers\Config\ChecklistTemplateController;
use App\Http\Controllers\Config\FeeScheduleController;
use App\Http\Controllers\Config\LgaController;
use App\Http\Controllers\Config\LicenseTypeController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:login')
        ->name('login.store');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/password/force-change', [AuthenticatedSessionController::class, 'showForcePasswordChange'])
        ->name('password.force-change');
    Route::put('/password/force-change', [AuthenticatedSessionController::class, 'updateForcePasswordChange'])
        ->name('password.force-change.update');

    Route::middleware('password.changed')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Phase 1.1: User & Role Management
        Route::prefix('admin')->name('admin.')->group(function () {
            Route::get('/users', [UserController::class, 'index'])->name('users.index');
            Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
            Route::post('/users', [UserController::class, 'store'])->name('users.store');
            Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
            Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
            Route::post('/users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
            Route::post('/users/{user}/reactivate', [UserController::class, 'reactivate'])->name('users.reactivate');
            Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
            Route::post('/users/{user}/force-logout', [UserController::class, 'forceLogout'])->name('users.force-logout');

            // Phase 1.2: Read-Only Audit Trail
            Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
            Route::get('/audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit-logs.show');
        });

        // Phase 1.3: System Configuration
        Route::prefix('config')->name('config.')->group(function () {
            Route::resource('lgas', LgaController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::resource('asset-categories', AssetCategoryController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::resource('license-types', LicenseTypeController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::resource('fee-schedules', FeeScheduleController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::resource('checklist-templates', ChecklistTemplateController::class)->only(['index', 'store', 'update', 'destroy']);
        });
    });
});
