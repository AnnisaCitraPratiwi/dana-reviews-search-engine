<?php

use App\Http\Controllers\ReviewSearchController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->group(function () {
    Route::post('/search', [ReviewSearchController::class, 'search']);
    Route::get('/stats', [ReviewSearchController::class, 'stats']);
    Route::post('/refresh-data', [ReviewSearchController::class, 'refreshData']);
    Route::get('/health', [ReviewSearchController::class, 'healthCheck']);
});