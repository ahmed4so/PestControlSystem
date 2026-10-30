<?php

declare(strict_types=1);

namespace App\Http\Responses\Auth;

use App\Domain\Identity\Enums\AuthArea;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\FailedTwoFactorLoginResponse as FailedTwoFactorLoginResponseContract;

/**
 * Fortify's failed 2FA response, sent back to the current area's challenge
 * page instead of a hard-coded route (D-079).
 */
final class FailedTwoFactorLoginResponse implements FailedTwoFactorLoginResponseContract
{
    /**
     * @param  Request  $request
     */
    public function toResponse($request): RedirectResponse
    {
        [$key, $message] = $request->filled('recovery_code')
            ? ['recovery_code', __('The provided two factor recovery code was invalid.')]
            : ['code', __('The provided two factor authentication code was invalid.')];

        $area = AuthArea::current($request) ?? AuthArea::Company;

        return redirect()->route($area->route('two-factor.login'))->withErrors([$key => $message]);
    }
}
