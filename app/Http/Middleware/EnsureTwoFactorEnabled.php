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
 * logout are reachable until then. Local development may switch this off
 * (D-185).
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

        if (! self::isEnforced() || ! $user instanceof User || $user->two_factor_confirmed_at !== null) {
            return $next($request);
        }

        $allowed = array_map(fn (string $name): string => AuthArea::Admin->route($name), self::ALLOWED_ROUTES);

        if ($request->routeIs(...$allowed)) {
            return $next($request);
        }

        return redirect()->route(AuthArea::Admin->route('two-factor.show'))
            ->with('status', __('Set up two-factor authentication to continue.'));
    }

    /**
     * Always enforced, except on a local development machine whose .env
     * turns it off (D-185). Staging and production ignore the switch.
     */
    public static function isEnforced(): bool
    {
        return ! app()->environment('local') || (bool) config('identity.staff_two_factor_required');
    }
}
