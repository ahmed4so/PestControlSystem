<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Domain\Identity\Enums\AuthArea;
use Illuminate\Http\Exceptions\HttpResponseException;
use Laravel\Fortify\Contracts\TwoFactorChallengeViewResponse;
use Laravel\Fortify\Http\Controllers\TwoFactorAuthenticatedSessionController;
use Laravel\Fortify\Http\Requests\TwoFactorLoginRequest;

/**
 * Fortify's 2FA challenge, with the "no pending login" redirect sent to the
 * current area's login page (D-079).
 */
final class TwoFactorChallengeController extends TwoFactorAuthenticatedSessionController
{
    public function create(TwoFactorLoginRequest $request): TwoFactorChallengeViewResponse
    {
        if (! $request->hasChallengedUser()) {
            $area = AuthArea::current($request) ?? AuthArea::Company;

            throw new HttpResponseException(redirect()->route($area->route('login')));
        }

        return app(TwoFactorChallengeViewResponse::class);
    }
}
