<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Settings;

use App\Domain\Identity\Actions\ChangePassword;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Settings\PasswordUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PasswordController extends Controller
{
    public function edit(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        return Inertia::render('admin/settings/password', [
            'mustChange' => $user->mustChangePassword(),
            'firstLogin' => $user->first_login,
            'expiresAt' => $user->passwordExpiresAt()?->toIso8601String(),
        ]);
    }

    public function update(PasswordUpdateRequest $request, ChangePassword $changePassword): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $changePassword($user, $request->string('password')->toString(), $request->session()->getId());

        // The user has just proved their password; the 2FA set-up that
        // follows on first login should not ask for it again.
        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->route('admin.dashboard')->with('success', __('Your password was changed.'));
    }
}
