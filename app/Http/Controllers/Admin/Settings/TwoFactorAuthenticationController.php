<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Settings\TwoFactorAuthenticationRequest;
use App\Models\User;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Staff 2FA set-up page. 2FA is mandatory for staff and cannot be disabled
 * (03 §8, D-052).
 */
class TwoFactorAuthenticationController extends Controller implements HasMiddleware
{
    /**
     * @return list<Middleware>
     */
    public static function middleware(): array
    {
        return [new Middleware('password.confirm:admin.password.confirm', only: ['show'])];
    }

    public function show(TwoFactorAuthenticationRequest $request): Response
    {
        $request->ensureStateIsValid();

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('admin/settings/two-factor', [
            'twoFactorEnabled' => $user->hasEnabledTwoFactorAuthentication(),
            'requiresConfirmation' => true,
            'canDisable' => false,
            'status' => $request->session()->get('status'),
        ]);
    }
}
