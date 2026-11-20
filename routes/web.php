<?php

use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnergyAnalysisController;
use App\Http\Controllers\GeographyController;
use App\Http\Controllers\PlanningReportController;
use App\Http\Controllers\PublicReportController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

// Root redirects to Dashboard if authenticated, or Public Portal landing
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('public.report.create');
})->name('home');

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/login/demo', [AuthController::class, 'quickDemoLogin'])->name('login.demo');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Category 3: Public Electricity Reporting Portal (No Auth Required)
Route::get('/report-challenge', [PublicReportController::class, 'create'])->name('public.report.create');
Route::post('/report-challenge', [PublicReportController::class, 'store'])->name('public.report.store');
Route::get('/track-report', [PublicReportController::class, 'track'])->name('public.report.track');

// Authenticated Platform Modules
Route::middleware(['auth'])->group(function () {
    // Category 1: Dashboard and Overview
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Category 2: Geographic and Community Management
    Route::get('/geography', [GeographyController::class, 'map'])->name('geography.index');
    Route::get('/communities/{community}', [GeographyController::class, 'showCommunity'])->name('communities.show');

    Route::middleware(['role.access:admin|lga_officer'])->group(function () {
        Route::post('/geography/lgas', [GeographyController::class, 'storeLga'])->name('geography.lgas.store');
        Route::post('/geography/wards', [GeographyController::class, 'storeWard'])->name('geography.wards.store');
        Route::post('/geography/communities', [GeographyController::class, 'storeCommunity'])->name('geography.communities.store');
        Route::put('/communities/{community}', [GeographyController::class, 'updateCommunity'])->name('communities.update');
    });

    // Category 4F: Public Report Verification (LGA Officer & Admin)
    Route::middleware(['role.access:admin|lga_officer|field_officer'])->group(function () {
        Route::get('/verification-queue', [PublicReportController::class, 'verificationQueue'])->name('reports.verification');
        Route::post('/verification-queue/{report}', [PublicReportController::class, 'updateVerificationStatus'])->name('reports.verify.update');
    });

    // Category 4: Field Data Collection & Assessment
    Route::get('/assessments', [AssessmentController::class, 'index'])->name('assessments.index');
    Route::middleware(['role.access:admin|field_officer|lga_officer'])->group(function () {
        Route::get('/assessments/create', [AssessmentController::class, 'create'])->name('assessments.create');
        Route::post('/assessments', [AssessmentController::class, 'store'])->name('assessments.store');
    });
    Route::get('/assessments/{assessment}', [AssessmentController::class, 'show'])->name('assessments.show');

    // Category 5 & 6: Energy Needs Assessment, Renewable Energy Recommendation & Cost Estimation
    Route::get('/energy-analysis', [EnergyAnalysisController::class, 'index'])->name('energy.index');
    Route::middleware(['role.access:admin|energy_analyst'])->group(function () {
        Route::post('/energy-analysis/appliances', [EnergyAnalysisController::class, 'storeAppliance'])->name('energy.appliances.store');
        Route::post('/energy-analysis/demands/{energyDemand}/recalculate', [EnergyAnalysisController::class, 'recalculateDemand'])->name('energy.demands.recalculate');
        Route::put('/energy-analysis/recommendations/{recommendation}', [EnergyAnalysisController::class, 'updateRecommendationAndCost'])->name('energy.recommendations.update');
    });

    // Category 7: Planning, Prioritisation & Reporting
    Route::get('/planning-reports', [PlanningReportController::class, 'index'])->name('planning.index');
    Route::post('/planning-reports/recalculate', [PlanningReportController::class, 'recalculateRankings'])
        ->middleware(['role.access:admin|energy_analyst'])
        ->name('planning.recalculate');
    Route::get('/planning-reports/export/excel', [PlanningReportController::class, 'exportExcel'])->name('planning.export.excel');
    Route::get('/planning-reports/export/pdf', [PlanningReportController::class, 'exportPriorityPdf'])->name('planning.export.pdf');
    Route::get('/communities/{community}/export/pdf', [PlanningReportController::class, 'exportCommunityPdf'])->name('communities.export.pdf');

    // Phase 3: User & Role Administration (Admin only)
    Route::middleware(['role.access:admin'])->group(function () {
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::put('/users/{user}/role', [UserManagementController::class, 'updateRole'])->name('users.role.update');
    });
});
