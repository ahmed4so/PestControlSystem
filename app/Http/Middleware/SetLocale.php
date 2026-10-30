<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\CompanyUser;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * UI language (D-032, D-169): the logged-in account's preferred_locale,
 * otherwise the guest's `locale` cookie, otherwise English.
 */
class SetLocale
{
    public const COOKIE = 'locale';

    public const SUPPORTED = ['en', 'so'];

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $locale = $user instanceof User || $user instanceof CompanyUser
            ? $user->preferredLocale()
            : $request->cookie(self::COOKIE);

        app()->setLocale(in_array($locale, self::SUPPORTED, true) ? $locale : 'en');

        return $next($request);
    }
}
