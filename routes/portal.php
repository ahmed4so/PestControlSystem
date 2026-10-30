<?php

declare(strict_types=1);

use App\Domain\Identity\Enums\AuthArea;
use App\Domain\Identity\Support\FortifyRoutes;
use App\Http\Controllers\Portal\Auth\SignUpController;
use App\Http\Controllers\Portal\Auth\TemporaryPasswordController;
use App\Http\Controllers\Portal\DashboardController;
use App\Http\Controllers\Portal\Settings\LoginEmailController;
use App\Http\Controllers\Portal\Settings\PasswordController;
use App\Http\Controllers\Portal\Settings\ProfileController;
use App\Http\Controllers\Portal\Settings\TwoFactorAuthenticationController;
use Illuminate\Support\Facades\Route;
use Spatie\Honeypot\ProtectAgainstSpam;

/*
| Company Portal: companies only, guard `company`, cookie
| `saris_portal_session` (03 §2, D-048, D-166). Auth pages sit at the root,
| app pages under /portal. Route names start with `portal.`.
*/

FortifyRoutes::authentication(AuthArea::Company);

Route::middleware('guest:company')->group(function (): void {
    Route::get('signup', [SignUpController::class, 'create'])->name('signup');
    Route::post('signup', [SignUpController::class, 'store'])
        ->middleware([ProtectAgainstSpam::class, 'throttle:signup'])
        ->name('signup.store');

    Route::get('temporary-password', [TemporaryPasswordController::class, 'create'])->name('temporary-password');
    Route::post('temporary-password', [TemporaryPasswordController::class, 'store'])
        ->middleware([ProtectAgainstSpam::class, 'throttle:temporary-password'])
        ->name('temporary-password.store');
});

// The confirmation link works whether or not the account is logged in.
Route::get('portal/settings/email/confirm/{token}', [LoginEmailController::class, 'confirm'])
    ->middleware('throttle:6,1')
    ->name('email.confirm');

Route::prefix('portal')->middleware(['auth:company', 'active', 'session.timeouts', 'password.current'])->group(function (): void {
    FortifyRoutes::account(AuthArea::Company);

    Route::get('/', DashboardController::class)->name('dashboard');

    Route::redirect('settings', '/portal/settings/profile')->name('settings');
    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('settings/password', [PasswordController::class, 'edit'])->name('user-password.edit');
    Route::put('settings/password', [PasswordController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');
    Route::get('settings/two-factor', [TwoFactorAuthenticationController::class, 'show'])->name('two-factor.show');

    // Login email change (D-093), after password confirmation.
    Route::middleware('password.confirm:portal.password.confirm')->group(function (): void {
        Route::get('settings/email', [LoginEmailController::class, 'edit'])->name('email.edit');
        Route::post('settings/email', [LoginEmailController::class, 'store'])
            ->middleware('throttle:6,1')
            ->name('email.store');
    });
});
