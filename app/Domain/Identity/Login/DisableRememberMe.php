<?php

declare(strict_types=1);

namespace App\Domain\Identity\Login;

use Closure;
use Illuminate\Http\Request;

/**
 * Login pipeline step: "remember me" is disabled in both areas (D-084).
 */
final class DisableRememberMe
{
    public function handle(Request $request, Closure $next): mixed
    {
        $request->request->remove('remember');
        $request->query->remove('remember');

        return $next($request);
    }
}
