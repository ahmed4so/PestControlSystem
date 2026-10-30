<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domain\Identity\Enums\AuthArea;
use App\Models\CompanyUser;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sends the account to its password page until it sets a new password: on
 * first login with a temporary password, and once the password is older
 * than 90 days (03 §4, §6; D-082). Replaces ForcePasswordChange.
 */
class EnsurePasswordIsCurrent
{
    private const ALLOWED_ROUTES = [
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

        if ((! $user instanceof User && ! $user instanceof CompanyUser) || ! $user->mustChangePassword()) {
            return $next($request);
        }

        $area = AuthArea::current($request) ?? $user->area();
        $allowed = array_map(fn (string $name): string => $area->route($name), self::ALLOWED_ROUTES);

        if ($request->routeIs(...$allowed)) {
            return $next($request);
        }

        return redirect()->route($area->route('user-password.edit'));
    }
}
