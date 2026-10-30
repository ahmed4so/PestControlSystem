<?php

declare(strict_types=1);

namespace App\Providers;

use App\Actions\Fortify\ResetUserPassword;
use App\Domain\Identity\Enums\AuthArea;
use App\Domain\Identity\Listeners\RecordSuccessfulLogin;
use App\Domain\Identity\Listeners\RecordTwoFactorChange;
use App\Domain\Identity\Login\AuthenticateAccount;
use App\Domain\Identity\Login\DisableRememberMe;
use App\Domain\Identity\Login\RedirectToAreaTwoFactorChallenge;
use App\Domain\Identity\Support\ApplyAuthArea;
use App\Http\Responses\Auth\FailedTwoFactorLoginResponse;
use App\Http\Responses\Auth\PasswordResetLinkRequestedResponse;
use App\Http\Responses\Auth\PasswordResetResponse;
use Illuminate\Auth\Events\Login;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Fortify\Actions\AttemptToAuthenticate;
use Laravel\Fortify\Actions\CanonicalizeUsername;
use Laravel\Fortify\Actions\PrepareAuthenticatedSession;
use Laravel\Fortify\Contracts\FailedPasswordResetLinkRequestResponse;
use Laravel\Fortify\Contracts\FailedTwoFactorLoginResponse as FailedTwoFactorLoginResponseContract;
use Laravel\Fortify\Contracts\PasswordResetResponse as PasswordResetResponseContract;
use Laravel\Fortify\Contracts\SuccessfulPasswordResetLinkRequestResponse;
use Laravel\Fortify\Events\RecoveryCodeReplaced;
use Laravel\Fortify\Events\TwoFactorAuthenticationConfirmed;
use Laravel\Fortify\Events\TwoFactorAuthenticationDisabled;
use Laravel\Fortify\Fortify;
use Spatie\Honeypot\Honeypot;

/**
 * Fortify for both portals (03 §3, D-062, D-079): SetAuthArea chooses the
 * guard and broker per request; this provider supplies the area's pages,
 * the login pipeline with lockout, and area-aware responses.
 */
class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Fortify::ignoreRoutes();

        // Fortify's controllers receive the guard when they are built, which
        // is before any route middleware runs (D-079).
        $this->app->bind(StatefulGuard::class, function (): StatefulGuard {
            $area = AuthArea::current(request());

            /** @var StatefulGuard */
            return Auth::guard($area?->guard() ?? config('fortify.guard'));
        });

        $this->app->singleton(FailedTwoFactorLoginResponseContract::class, FailedTwoFactorLoginResponse::class);
        $this->app->bind(PasswordResetResponseContract::class, PasswordResetResponse::class);
        $this->app->singleton(SuccessfulPasswordResetLinkRequestResponse::class, PasswordResetLinkRequestedResponse::class);
        $this->app->singleton(FailedPasswordResetLinkRequestResponse::class, PasswordResetLinkRequestedResponse::class);
    }

    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();
        $this->configureEvents();
    }

    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        Fortify::authenticateUsing(fn (Request $request) => app(AuthenticateAccount::class)($request));

        Fortify::authenticateThrough(fn (Request $request): array => [
            DisableRememberMe::class,
            CanonicalizeUsername::class,
            RedirectToAreaTwoFactorChallenge::class,
            AttemptToAuthenticate::class,
            PrepareAuthenticatedSession::class,
        ]);
    }

    private function configureViews(): void
    {
        Fortify::loginView(fn (Request $request) => Inertia::render($this->page($request, 'auth/login'), [
            'status' => $request->session()->get('status'),
        ]));

        Fortify::resetPasswordView(fn (Request $request) => Inertia::render($this->page($request, 'auth/reset-password'), [
            'email' => $request->query('email'),
            'token' => $request->route('token'),
        ]));

        Fortify::requestPasswordResetLinkView(fn (Request $request) => Inertia::render($this->page($request, 'auth/forgot-password'), [
            'status' => $request->session()->get('status'),
            'honeypot' => app(Honeypot::class)->toArray(),
        ]));

        Fortify::twoFactorChallengeView(fn (Request $request) => Inertia::render($this->page($request, 'auth/two-factor-challenge')));

        Fortify::confirmPasswordView(fn (Request $request) => Inertia::render($this->page($request, 'auth/confirm-password')));
    }

    /**
     * Limits per area, so attempts on one portal never affect the other
     * (03 §3 step 5, §14).
     */
    private function configureRateLimiting(): void
    {
        $area = fn (Request $request): string => (AuthArea::current($request) ?? AuthArea::Company)->value;
        $email = fn (Request $request): string => Str::transliterate(Str::lower((string) $request->input('email')));

        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(5)
            ->by($area($request).'|'.$email($request).'|'.$request->ip()));

        RateLimiter::for('two-factor', fn (Request $request) => Limit::perMinute(5)
            ->by($area($request).'|'.($request->session()->get('login.id') ?? $request->user()?->getAuthIdentifier() ?? $request->ip())));

        RateLimiter::for('signup', fn (Request $request) => Limit::perHour(3)->by('signup|'.$request->ip()));

        RateLimiter::for('temporary-password', fn (Request $request) => Limit::perHour(3)
            ->by('temporary-password|'.$email($request)));

        RateLimiter::for('forgot-password', fn (Request $request) => Limit::perHour(3)
            ->by($area($request).'|forgot-password|'.$email($request)));
    }

    private function configureEvents(): void
    {
        // Apply the area as soon as the route is known: Laravel builds the
        // controller (and with it the guard and session store) before the
        // route middleware runs (D-079).
        Event::listen(RouteMatched::class, function (RouteMatched $event): void {
            $area = AuthArea::forRoute($event->route);

            if ($area !== null) {
                ApplyAuthArea::to($event->request, $area);
            }
        });

        Event::listen(Login::class, RecordSuccessfulLogin::class);
        Event::listen(TwoFactorAuthenticationConfirmed::class, [RecordTwoFactorChange::class, 'enabled']);
        Event::listen(TwoFactorAuthenticationDisabled::class, [RecordTwoFactorChange::class, 'disabled']);
        Event::listen(RecoveryCodeReplaced::class, [RecordTwoFactorChange::class, 'recoveryCodeUsed']);
    }

    private function page(Request $request, string $page): string
    {
        return (AuthArea::current($request) ?? AuthArea::Company)->pages().'/'.$page;
    }
}
