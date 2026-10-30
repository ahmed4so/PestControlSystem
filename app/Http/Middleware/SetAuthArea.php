<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domain\Identity\Enums\AuthArea;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies one area's guard, password broker, home, session cookie, table
 * and timeouts to the current request (03-auth-security §3, D-079). It must
 * run before StartSession, so each portal reads only its own cookie.
 */
final class SetAuthArea
{
    public function __construct(private readonly SessionManager $sessions) {}

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $area): Response
    {
        $area = AuthArea::from($area);

        $request->attributes->set(AuthArea::REQUEST_ATTRIBUTE, $area);

        config([
            'fortify.guard' => $area->guard(),
            'fortify.passwords' => $area->broker(),
            'fortify.home' => $area->home(),
            'fortify.redirects.logout' => route($area->route('login'), absolute: false),
            'auth.defaults.guard' => $area->guard(),
            'auth.defaults.passwords' => $area->broker(),
            'auth.password_timeout' => $area->passwordConfirmSeconds(),
            'session.cookie' => $area->sessionCookie(),
            'session.table' => $area->sessionTable(),
            'session.path' => $area->sessionPath(),
            'session.lifetime' => $area->idleMinutes(),
        ]);

        Auth::shouldUse($area->guard());

        $this->renameSessionStoreOfOtherArea($area);

        return $next($request);
    }

    /**
     * A session store created earlier in the same process (only in tests)
     * still carries the other area's cookie name. In production every
     * request builds its store after this middleware has run.
     */
    private function renameSessionStoreOfOtherArea(AuthArea $area): void
    {
        foreach ($this->sessions->getDrivers() as $store) {
            if ($store->getName() !== $area->sessionCookie()) {
                $store->setName($area->sessionCookie());
            }
        }
    }
}
