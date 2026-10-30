<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domain\Identity\Enums\AuthArea;
use App\Models\CompanyUser;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Logs out an account whose status is no longer ACTIVE, on its next
 * request (D-053, 03 §9).
 */
class EnsureUserIsActive
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User && ! $user instanceof CompanyUser) {
            return $next($request);
        }

        if ($user->isActive()) {
            return $next($request);
        }

        $area = AuthArea::current($request) ?? $user->area();

        Auth::guard($area->guard())->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route($area->route('login'))->with(
            'status',
            __('Your account is not active. Please contact SARIS.'),
        );
    }
}
