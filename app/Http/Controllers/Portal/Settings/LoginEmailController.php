<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal\Settings;

use App\Domain\Identity\Actions\ConfirmLoginEmailChange;
use App\Domain\Identity\Actions\RequestLoginEmailChange;
use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\Settings\ChangeLoginEmailRequest;
use App\Models\CompanyUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Company login-email change (03 §5.1, D-093).
 */
class LoginEmailController extends Controller
{
    public function edit(Request $request): Response
    {
        /** @var CompanyUser $account */
        $account = $request->user();

        return Inertia::render('portal/settings/email', [
            'email' => $account->email,
            'pendingEmail' => $account->pending_email,
        ]);
    }

    public function store(ChangeLoginEmailRequest $request, RequestLoginEmailChange $requestChange): RedirectResponse
    {
        /** @var CompanyUser $account */
        $account = $request->user();

        $requestChange($account, $request->string('email')->toString());

        return back()->with('success', __('We sent a confirmation link to the new email. Your login email changes when you open it.'));
    }

    public function confirm(string $token, ConfirmLoginEmailChange $confirm): RedirectResponse
    {
        $changed = $confirm($token);
        $route = auth('company')->check() ? 'portal.profile.edit' : 'portal.login';

        return redirect()->route($route)->with(
            $changed ? 'success' : 'error',
            $changed
                ? __('Your login email was changed.')
                : __('This link is not valid or has expired. Request the change again.'),
        );
    }
}
