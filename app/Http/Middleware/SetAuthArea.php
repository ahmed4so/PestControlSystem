<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domain\Identity\Enums\AuthArea;
use App\Domain\Identity\Support\ApplyAuthArea;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies the route group's area (guard, broker, home, session cookie and
 * table, timeouts) before StartSession (03-auth-security §3, D-079). The
 * same settings are also applied when the route is matched; see
 * ApplyAuthArea.
 */
final class SetAuthArea
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $area): Response
    {
        ApplyAuthArea::to($request, AuthArea::from($area));

        return $next($request);
    }
}
