<?php

use App\Http\Controllers\Api\PlatformApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/lgas', [PlatformApiController::class, 'lgas'])->name('api.lgas');
    Route::get('/lgas/{lga}/wards', [PlatformApiController::class, 'wardsByLga'])->name('api.lga.wards');
    Route::get('/wards/{ward}/communities', [PlatformApiController::class, 'communitiesByWard'])->name('api.ward.communities');
    Route::get('/map/communities', [PlatformApiController::class, 'mapCommunities'])->name('api.map.communities');
    Route::get('/reports/{referenceNumber}', [PlatformApiController::class, 'trackReport'])->name('api.reports.track');
});
