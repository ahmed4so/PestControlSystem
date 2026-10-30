<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal\Settings;

use App\Domain\Identity\Actions\ChangePassword;
use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\Settings\PasswordUpdateRequest;
use App\Models\CompanyUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PasswordController extends Controller
{
    public function edit(Request $request): Response
    {
        /** @var CompanyUser $account */
        $account = $request->user();

        return Inertia::render('portal/settings/password', [
            'mustChange' => $account->mustChangePassword(),
            'firstLogin' => $account->first_login,
            'expiresAt' => $account->passwordExpiresAt()?->toIso8601String(),
        ]);
    }

    public function update(PasswordUpdateRequest $request, ChangePassword $changePassword): RedirectResponse
    {
        /** @var CompanyUser $account */
        $account = $request->user();

        $changePassword($account, $request->string('password')->toString(), $request->session()->getId());

        return redirect()->route('portal.dashboard')->with('success', __('Your password was changed.'));
    }
}
