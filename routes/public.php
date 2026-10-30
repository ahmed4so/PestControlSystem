<?php

declare(strict_types=1);

use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\LocaleController;
use Illuminate\Support\Facades\Route;

/*
| Public pages, no login (D-048, D-166, D-169).
*/

Route::get('/', HomeController::class)->name('home');

Route::post('locale/{locale}', LocaleController::class)
    ->whereIn('locale', ['en', 'so'])
    ->middleware('throttle:30,1')
    ->name('locale.update');
