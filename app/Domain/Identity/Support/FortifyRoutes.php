<?php

declare(strict_types=1);

namespace App\Domain\Identity\Support;

use App\Domain\Identity\Enums\AuthArea;
use App\Http\Controllers\Auth\TwoFactorChallengeController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\Http\Controllers\ConfirmablePasswordController;
use Laravel\Fortify\Http\Controllers\ConfirmedPasswordStatusController;
use Laravel\Fortify\Http\Controllers\ConfirmedTwoFactorAuthenticationController;
use Laravel\Fortify\Http\Controllers\NewPasswordController;
use Laravel\Fortify\Http\Controllers\PasswordResetLinkController;
use Laravel\Fortify\Http\Controllers\RecoveryCodeController;
use Laravel\Fortify\Http\Controllers\TwoFactorAuthenticationController;
use Laravel\Fortify\Http\Controllers\TwoFactorQrCodeController;
use Laravel\Fortify\Http\Controllers\TwoFactorSecretKeyController;
use Spatie\Honeypot\ProtectAgainstSpam;

/**
 * Registers Fortify's own controllers in each area's route file, because
 * Fortify serves one guard and its default routes are ignored (03 §3,
 * D-062, D-079). Call it inside the area's named route group, so the names
 * become `admin.login`, `portal.login` and so on.
 */
final class FortifyRoutes
{
    /**
     * Login, logout, password reset and the 2FA challenge.
     */
    public static function authentication(AuthArea $area): void
    {
        Route::middleware('guest:'.$area->guard())->group(function (): void {
            Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
            Route::post('login', [AuthenticatedSessionController::class, 'store'])
                ->middleware('throttle:login')
                ->name('login.store');

            Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
            Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
                ->middleware([ProtectAgainstSpam::class, 'throttle:forgot-password'])
                ->name('password.email');

            Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
            Route::post('reset-password', [NewPasswordController::class, 'store'])
                ->middleware('throttle:6,1')
                ->name('password.update');

            Route::get('two-factor-challenge', [TwoFactorChallengeController::class, 'create'])->name('two-factor.login');
            Route::post('two-factor-challenge', [TwoFactorChallengeController::class, 'store'])
                ->middleware('throttle:two-factor')
                ->name('two-factor.login.store');
        });

        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
            ->middleware('auth:'.$area->guard())
            ->name('logout');
    }

    /**
     * Password confirmation and 2FA management for a logged-in account.
     * Staff may not disable 2FA (03 §8).
     */
    public static function account(AuthArea $area): void
    {
        $confirm = 'password.confirm:'.$area->route('password.confirm');

        Route::get('user/confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
        Route::post('user/confirm-password', [ConfirmablePasswordController::class, 'store'])
            ->middleware('throttle:6,1')
            ->name('password.confirm.store');
        Route::get('user/confirmed-password-status', [ConfirmedPasswordStatusController::class, 'show'])
            ->name('password.confirmation');

        Route::middleware($confirm)->group(function () use ($area): void {
            Route::post('user/two-factor-authentication', [TwoFactorAuthenticationController::class, 'store'])
                ->name('two-factor.enable');
            Route::post('user/confirmed-two-factor-authentication', [ConfirmedTwoFactorAuthenticationController::class, 'store'])
                ->middleware('throttle:two-factor')
                ->name('two-factor.confirm');

            if ($area === AuthArea::Company) {
                Route::delete('user/two-factor-authentication', [TwoFactorAuthenticationController::class, 'destroy'])
                    ->name('two-factor.disable');
            }

            Route::get('user/two-factor-qr-code', [TwoFactorQrCodeController::class, 'show'])->name('two-factor.qr-code');
            Route::get('user/two-factor-secret-key', [TwoFactorSecretKeyController::class, 'show'])->name('two-factor.secret-key');
            Route::get('user/two-factor-recovery-codes', [RecoveryCodeController::class, 'index'])->name('two-factor.recovery-codes');
            Route::post('user/two-factor-recovery-codes', [RecoveryCodeController::class, 'store'])
                ->name('two-factor.regenerate-recovery-codes');
        });
    }
}
