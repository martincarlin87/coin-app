<?php

use App\Http\Controllers\Web\CoinController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CoinController::class, 'index'])->name('home');

Route::get('/coins', [CoinController::class, 'index'])->name('coins.index');
Route::get('/coins/{coin}', [CoinController::class, 'show'])->name('coins.show');
