<?php

declare(strict_types=1);

namespace App\Http\Responses\Auth;

use App\Domain\Identity\Support\PublicFormMessages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\FailedPasswordResetLinkRequestResponse;
use Laravel\Fortify\Contracts\SuccessfulPasswordResetLinkRequestResponse;

/**
 * The same answer whether or not the email has an account, and whether or
 * not a link was just sent (03 §4, D-080): no enumeration.
 */
final class PasswordResetLinkRequestedResponse implements FailedPasswordResetLinkRequestResponse, SuccessfulPasswordResetLinkRequestResponse
{
    /**
     * @param  Request  $request
     */
    public function toResponse($request): RedirectResponse
    {
        return back()->with('status', __(PublicFormMessages::FORGOT_PASSWORD));
    }
}
