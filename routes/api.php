<?php

use App\Http\Controllers\Api\CoinApiController;
use Illuminate\Support\Facades\Route;

// CORS protection: Only APP_URL domain can access (config/cors.php)
// CSRF protection: Handled by statefulApi middleware (bootstrap/app.php)
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/coins', [CoinApiController::class, 'index']);
    Route::get('/coins/{coin}', [CoinApiController::class, 'show']);
});
