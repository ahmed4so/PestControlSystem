<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\Settings\ProfileUpdateRequest;
use App\Models\CompanyUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        /** @var CompanyUser $account */
        $account = $request->user();

        return Inertia::render('portal/settings/profile', [
            'account' => $account->only(['full_name', 'email', 'phone', 'preferred_locale', 'pending_email']),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        /** @var CompanyUser $account */
        $account = $request->user();

        $account->update($request->validated());

        return to_route('portal.profile.edit')->with('success', __('Your details were saved.'));
    }
}
