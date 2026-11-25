<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Config\AssetCategoryController;
use App\Http\Controllers\Config\ChecklistTemplateController;
use App\Http\Controllers\Config\FeeScheduleController;
use App\Http\Controllers\Config\LgaController;
use App\Http\Controllers\Config\LicenseTypeController;
use App\Http\Controllers\Culture\CulturalAssetController;
use App\Http\Controllers\Culture\IntangibleHeritageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeveloperInfoController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\Licensing\ComplianceDashboardController;
use App\Http\Controllers\Licensing\HotelController;
use App\Http\Controllers\Licensing\InspectionController;
use App\Http\Controllers\Licensing\LicenseApplicationController;
use App\Http\Controllers\Licensing\LicenseFeeController;
use App\Http\Controllers\Projects\ProjectController;
use App\Http\Controllers\Reports\AdHocQueryController;
use App\Http\Controllers\Reports\ReportingController;
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
        Route::get('/developer', [DeveloperInfoController::class, 'index'])->name('developer.info');

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

        // Phase 2: Cultural & Tourism Resource Registry
        Route::prefix('culture')->name('culture.')->group(function () {
            Route::get('/assets/map', [CulturalAssetController::class, 'map'])->name('assets.map');
            Route::get('/assets/export', [CulturalAssetController::class, 'export'])->name('assets.export');
            Route::get('/assets/import', [CulturalAssetController::class, 'showImport'])->name('assets.import.form');
            Route::post('/assets/import', [CulturalAssetController::class, 'import'])->name('assets.import');
            Route::get('/assets/template', [CulturalAssetController::class, 'downloadTemplate'])->name('assets.template');
            Route::resource('assets', CulturalAssetController::class)->parameters(['assets' => 'cultural_asset']);

            Route::resource('heritage', IntangibleHeritageController::class)->parameters(['heritage' => 'intangible_heritage']);
        });

        // Phase 2.3 & 6.1: Central Document Repository & Polymorphic Media (Local Storage)
        Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
        Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
        Route::get('/documents/{document}', [DocumentController::class, 'show'])->name('documents.show');
        Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
        Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

        // Phase 6.2: Task Assignment & In-App Notification Center
        Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
        Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
        Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status');
        Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

        // Phase 6.3: Threaded Internal Notes (Polymorphic)
        Route::post('/notes', [NoteController::class, 'store'])->name('notes.store');
        Route::delete('/notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');

        // Phase 3: Hotel & Hospitality Licensing Workflow
        Route::prefix('licensing')->name('licensing.')->group(function () {
            Route::get('/compliance', [ComplianceDashboardController::class, 'index'])->name('compliance.index');

            Route::resource('hotels', HotelController::class);

            Route::get('/applications', [LicenseApplicationController::class, 'index'])->name('applications.index');
            Route::get('/applications/create', [LicenseApplicationController::class, 'create'])->name('applications.create');
            Route::post('/applications', [LicenseApplicationController::class, 'store'])->name('applications.store');
            Route::get('/applications/{application}', [LicenseApplicationController::class, 'show'])->name('applications.show');
            Route::post('/applications/{application}/submit', [LicenseApplicationController::class, 'submit'])->name('applications.submit');
            Route::post('/applications/{application}/review', [LicenseApplicationController::class, 'startReview'])->name('applications.review');
            Route::post('/applications/{application}/approve', [LicenseApplicationController::class, 'approve'])->name('applications.approve');
            Route::post('/applications/{application}/reject', [LicenseApplicationController::class, 'reject'])->name('applications.reject');
            Route::post('/applications/{application}/renew', [LicenseApplicationController::class, 'renew'])->name('applications.renew');

            Route::get('/inspections', [InspectionController::class, 'index'])->name('inspections.index');
            Route::post('/inspections', [InspectionController::class, 'store'])->name('inspections.store');
            Route::get('/inspections/{inspection}', [InspectionController::class, 'show'])->name('inspections.show');
            Route::post('/inspections/{inspection}/complete', [InspectionController::class, 'complete'])->name('inspections.complete');

            Route::get('/fees', [LicenseFeeController::class, 'index'])->name('fees.index');
            Route::post('/fees', [LicenseFeeController::class, 'store'])->name('fees.store');
            Route::get('/fees/export', [LicenseFeeController::class, 'export'])->name('fees.export');
            Route::delete('/fees/{fee}', [LicenseFeeController::class, 'destroy'])->name('fees.destroy');
        });

        // Phase 4: Project & Program Tracking
        Route::resource('projects', ProjectController::class);
        Route::post('/projects/{project}/milestones', [ProjectController::class, 'storeMilestone'])->name('projects.milestones.store');
        Route::post('/projects/{project}/milestones/{milestone}/toggle', [ProjectController::class, 'completeMilestone'])->name('projects.milestones.toggle');
        Route::post('/projects/{project}/expenditures', [ProjectController::class, 'storeExpenditure'])->name('projects.expenditures.store');

        // Phase 5: Reporting & Decision-Support Dashboard
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/kpi', [ReportingController::class, 'kpiDashboard'])->name('kpi');
            Route::get('/standard', [ReportingController::class, 'standardReports'])->name('standard');
            Route::get('/standard/{reportKey}', [ReportingController::class, 'showStandardReport'])
                ->whereIn('reportKey', ['monthly-licensing', 'heritage-inventory', 'project-status', 'inspection-rates'])
                ->name('standard.show');
            Route::get('/standard/{reportKey}/{format}', [ReportingController::class, 'downloadStandardReport'])
                ->whereIn('format', ['pdf', 'xlsx'])
                ->name('standard.download');
            Route::get('/query-builder', [AdHocQueryController::class, 'index'])->name('query-builder');
        });
    });
});
