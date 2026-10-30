<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domain\Identity\Enums\AuthArea;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Staff must have confirmed TOTP 2FA before any admin page opens (03 §8,
 * D-052). Only 2FA set-up, password confirmation, the password page and
 * logout are reachable until then.
 */
class EnsureTwoFactorEnabled
{
    private const ALLOWED_ROUTES = [
        'two-factor.*',
        'password.confirm',
        'password.confirm.store',
        'password.confirmation',
        'user-password.edit',
        'user-password.update',
        'logout',
    ];

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User || $user->two_factor_confirmed_at !== null) {
            return $next($request);
        }

        $allowed = array_map(fn (string $name): string => AuthArea::Admin->route($name), self::ALLOWED_ROUTES);

        if ($request->routeIs(...$allowed)) {
            return $next($request);
        }

        return redirect()->route(AuthArea::Admin->route('two-factor.show'))
            ->with('status', __('Set up two-factor authentication to continue.'));
    }
}
