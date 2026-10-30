<?php

declare(strict_types=1);

namespace App\Domain\Identity\Support;

use App\Domain\Identity\Enums\AuthArea;
use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\Auth;

/**
 * Applies one area's guard, password broker, home, session cookie, table
 * and timeouts to the current request (03-auth-security §3, D-079).
 *
 * It runs twice with the same result: when the route is matched (before
 * Laravel builds the controller, which already resolves the guard and the
 * session store) and in SetAuthArea, before StartSession.
 */
final class ApplyAuthArea
{
    public static function to(Request $request, AuthArea $area): void
    {
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

        self::renameSessionStoreOfOtherArea($area);
    }

    /**
     * A session store created earlier in the same process (only in tests)
     * still carries the other area's cookie name. In production every
     * request builds its store after the area is applied.
     */
    private static function renameSessionStoreOfOtherArea(AuthArea $area): void
    {
        foreach (app(SessionManager::class)->getDrivers() as $store) {
            if ($store->getName() !== $area->sessionCookie()) {
                $store->setName($area->sessionCookie());
            }
        }
    }
}
