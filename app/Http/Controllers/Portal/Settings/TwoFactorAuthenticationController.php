<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\Settings\TwoFactorAuthenticationRequest;
use App\Models\CompanyUser;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Optional company 2FA (03 §8, D-064), after password confirmation.
 */
class TwoFactorAuthenticationController extends Controller implements HasMiddleware
{
    /**
     * @return list<Middleware>
     */
    public static function middleware(): array
    {
        return [new Middleware('password.confirm:portal.password.confirm', only: ['show'])];
    }

    public function show(TwoFactorAuthenticationRequest $request): Response
    {
        $request->ensureStateIsValid();

        /** @var CompanyUser $account */
        $account = $request->user();

        return Inertia::render('portal/settings/two-factor', [
            'twoFactorEnabled' => $account->hasEnabledTwoFactorAuthentication(),
            'requiresConfirmation' => true,
            'canDisable' => true,
        ]);
    }
}
