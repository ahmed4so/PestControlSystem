<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\CompanyUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * After sign-up and the first password change, the company sets up its
 * profile before anything else (03 §4, D-063). Settings and logout stay
 * reachable.
 */
class EnsureCompanyProfileExists
{
    private const ALLOWED_ROUTES = [
        'portal.company.create',
        'portal.company.store',
        'portal.profile.*',
        'portal.user-password.*',
        'portal.two-factor.*',
        'portal.password.*',
        'portal.email.*',
        'portal.logout',
    ];

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $account = $request->user();

        if (! $account instanceof CompanyUser || $account->company_id !== null || $request->routeIs(...self::ALLOWED_ROUTES)) {
            return $next($request);
        }

        return redirect()->route('portal.company.create');
    }
}
