<?php

declare(strict_types=1);

namespace App\Domain\Identity\Login;

use App\Domain\Identity\Enums\AuthArea;
use Illuminate\Http\Request;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Events\TwoFactorAuthenticationChallenged;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fortify's 2FA step, redirecting to the current area's challenge page
 * (D-079). "Remember me" is never kept (D-084).
 */
final class RedirectToAreaTwoFactorChallenge extends RedirectIfTwoFactorAuthenticatable
{
    /**
     * @param  Request  $request
     * @param  mixed  $user
     * @return Response
     */
    protected function twoFactorChallengeResponse($request, $user)
    {
        $request->session()->put([
            'login.id' => $user->getKey(),
            'login.remember' => false,
        ]);

        TwoFactorAuthenticationChallenged::dispatch($user);

        $area = AuthArea::current($request) ?? AuthArea::Company;

        return redirect()->route($area->route('two-factor.login'));
    }
}
