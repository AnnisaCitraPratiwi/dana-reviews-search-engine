<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReviewSearchController;

// Web routes
Route::get('/', [ReviewSearchController::class, 'index'])->name('search.index');
Route::post('/', [ReviewSearchController::class, 'searchWeb'])->name('search.post'); // For traditional form submission

// API routes
Route::prefix('api')->group(function () {
    Route::post('/search', [ReviewSearchController::class, 'search'])->name('api.search');
    Route::get('/stats', [ReviewSearchController::class, 'stats'])->name('api.stats');
    Route::get('/health', [ReviewSearchController::class, 'healthCheck'])->name('api.health');
});