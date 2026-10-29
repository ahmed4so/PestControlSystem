<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Browser security headers (D-090, 03-auth-security §12.3). The nonce-based
 * Content-Security-Policy is added by spatie/laravel-csp; HSTS is set by Nginx.
 */
class SecurityHeaders
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (config('csp.enabled') && config('csp.nonce_enabled')) {
            Vite::useCspNonce(app('csp-nonce'));
        }

        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        // Camera and geolocation are opened per page when uploads and inspections
        // are built (03 §12.3); everything else stays off.
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()');

        return $response;
    }
}
