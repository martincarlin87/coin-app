<?php

use App\Http\Controllers\Api\CoinApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/coins', [CoinApiController::class, 'index']);
Route::get('/coins/{coin}', [CoinApiController::class, 'show']);
