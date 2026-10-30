<?php

declare(strict_types=1);

use App\Domain\Identity\Enums\AuthArea;
use App\Domain\Identity\Support\FortifyRoutes;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FailedEmailController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\Settings\PasswordController;
use App\Http\Controllers\Admin\Settings\ProfileController;
use App\Http\Controllers\Admin\Settings\TwoFactorAuthenticationController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
| Admin Portal (/admin/*): staff only, guard `web`, cookie
| `saris_admin_session` (03 §2, D-048). Route names start with `admin.`.
*/

FortifyRoutes::authentication(AuthArea::Admin);

Route::middleware(['auth:web', 'active', 'session.timeouts', 'password.current', 'two-factor.enforced'])->group(function (): void {
    FortifyRoutes::account(AuthArea::Admin);

    Route::get('/', DashboardController::class)->name('dashboard');

    // Settings
    Route::redirect('settings', '/admin/settings/profile')->name('settings');
    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('settings/password', [PasswordController::class, 'edit'])->name('user-password.edit');
    Route::put('settings/password', [PasswordController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');
    Route::get('settings/appearance', fn () => Inertia::render('admin/settings/appearance'))->name('appearance.edit');
    Route::get('settings/two-factor', [TwoFactorAuthenticationController::class, 'show'])->name('two-factor.show');

    // Staff users are deactivated, never deleted (D-053). Changes need a
    // recent password confirmation (D-085).
    Route::middleware('password.confirm:admin.password.confirm')->group(function (): void {
        Route::resource('users', UserController::class)->except(['destroy']);
        Route::post('users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
        Route::post('users/{user}/activate', [UserController::class, 'activate'])->name('users.activate');
        Route::post('users/{user}/unlock', [UserController::class, 'unlock'])->name('users.unlock');
        Route::post('users/{user}/reset-two-factor', [UserController::class, 'resetTwoFactor'])->name('users.reset-two-factor');
        Route::post('users/{user}/temporary-password', [UserController::class, 'sendTemporaryPassword'])
            ->middleware('throttle:6,1')
            ->name('users.temporary-password');

        Route::prefix('roles')->middleware('throttle:30,60')->group(function (): void {
            Route::get('permissions', [RoleController::class, 'permissions'])->name('roles.permissions');
            Route::post('permissions', [RoleController::class, 'assignPermissions'])->middleware('throttle:10,1')->name('roles.permissions.assign');
            Route::get('assign', [RoleController::class, 'assignRolesPage'])->name('roles.assign');
            Route::post('assign', [RoleController::class, 'assignRoles'])->name('roles.assign.store');
        });

        Route::resource('roles', RoleController::class)->except(['show'])->middleware('throttle:10,1');
    });

    // Emails that could not be delivered (D-129).
    Route::get('failed-emails', [FailedEmailController::class, 'index'])->name('failed-emails.index');
});
