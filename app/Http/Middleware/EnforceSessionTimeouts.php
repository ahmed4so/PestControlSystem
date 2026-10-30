<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domain\Identity\Enums\AuthArea;
use App\Domain\Identity\Listeners\RecordSuccessfulLogin;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Idle timeout (15 minutes staff, 30 minutes companies) and the staff
 * 12-hour absolute limit (03 §9, D-084). The session lifetime is also set
 * per area; this check makes the limits exact whatever the session driver.
 */
class EnforceSessionTimeouts
{
    public const LAST_ACTIVITY = 'auth.last_activity_at';

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $area = AuthArea::current($request);

        if ($area === null || $request->user() === null || ! $request->hasSession()) {
            return $next($request);
        }

        $session = $request->session();
        $now = now()->getTimestamp();
        $lastActivity = (int) $session->get(self::LAST_ACTIVITY, $now);
        $startedAt = (int) $session->get(RecordSuccessfulLogin::SESSION_STARTED_AT, $now);
        $absoluteHours = $area->absoluteHours();

        $idle = $now - $lastActivity > $area->idleMinutes() * 60;
        $tooLong = $absoluteHours !== null && $now - $startedAt > $absoluteHours * 3600;

        if ($idle || $tooLong) {
            Auth::guard($area->guard())->logout();
            $session->invalidate();
            $session->regenerateToken();

            return redirect()->route($area->route('login'))
                ->with('status', __('Your session has ended. Please log in again.'));
        }

        $session->put(self::LAST_ACTIVITY, $now);

        return $next($request);
    }
}
