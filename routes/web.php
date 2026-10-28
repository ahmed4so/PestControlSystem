<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Middleware\ForcePasswordChange;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

require __DIR__.'/settings.php';

Route::get('/', fn () => Inertia::render('welcome'))->name('home');

Route::middleware(['auth', 'active', 'verified', ForcePasswordChange::class])->group(function () {

    Route::get('dashboard', DashboardController::class)->name('dashboard');

    // Staff users are deactivated, never deleted (D-053).
    Route::resource('users', UserController::class)->except(['destroy']);
    Route::post('users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
    Route::post('users/{user}/activate', [UserController::class, 'activate'])->name('users.activate');

    // 120 requests per minute
    Route::prefix('roles')->middleware('throttle:30,60')->group(function () {
        Route::get('permissions', [RoleController::class, 'permissions'])->name('roles.permissions');
        Route::post('permissions', [RoleController::class, 'assignPermissions'])->middleware('throttle:10,1')->name('roles.permissions.assign');
        Route::get('assign', [RoleController::class, 'assignRolesPage'])->name('roles.assign');
        Route::post('assign', [RoleController::class, 'assignRoles'])->middleware('throttle:30,60')->name('roles.assign.store');
    });

    Route::resource('roles', RoleController::class)->except(['show'])->middleware('throttle:10,1');
});
