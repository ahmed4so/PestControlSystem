<?php

use App\Domain\Identity\Enums\AuthArea;
use App\Http\Middleware\EnforceSessionTimeouts;
use App\Http\Middleware\EnsurePasswordIsCurrent;
use App\Http\Middleware\EnsureTwoFactorEnabled;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetAuthArea;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Spatie\Csp\AddCspHeaders;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            // Each area sets its guard, broker and session cookie before the
            // session starts (03 §3, D-048, D-079).
            Route::middleware(['area:company', 'web'])->group(base_path('routes/public.php'));

            Route::middleware(['area:company', 'web'])
                ->name('portal.')
                ->group(base_path('routes/portal.php'));

            Route::middleware(['area:admin', 'web'])
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            SetLocale::class,
            SecurityHeaders::class,
            AddCspHeaders::class,
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        // SetAuthArea must run before StartSession, whatever the route's
        // middleware order (03 §3 step 4).
        $middleware->prependToPriorityList(before: StartSession::class, prepend: SetAuthArea::class);

        $middleware->alias([
            'area' => SetAuthArea::class,
            'permission' => PermissionMiddleware::class,
            'role' => RoleMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'active' => EnsureUserIsActive::class,
            'password.current' => EnsurePasswordIsCurrent::class,
            'two-factor.enforced' => EnsureTwoFactorEnabled::class,
            'session.timeouts' => EnforceSessionTimeouts::class,
        ]);

        $middleware->redirectGuestsTo(
            fn (Request $request): string => route((AuthArea::current($request) ?? AuthArea::Company)->route('login'))
        );

        $middleware->redirectUsersTo(
            fn (Request $request): string => (AuthArea::current($request) ?? AuthArea::Company)->home()
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
