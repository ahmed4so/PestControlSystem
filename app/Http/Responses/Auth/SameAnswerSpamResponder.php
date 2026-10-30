<?php

declare(strict_types=1);

namespace App\Http\Responses\Auth;

use App\Domain\Identity\Support\PublicFormMessages;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\Honeypot\SpamResponder\SpamResponder;

/**
 * A submission caught by the honeypot or the minimum fill time gets the
 * form's normal answer, and nothing is done (D-080).
 */
final class SameAnswerSpamResponder implements SpamResponder
{
    public function respond(Request $request, Closure $next): RedirectResponse
    {
        return back()->with('status', __(PublicFormMessages::forRoute($request->route()?->getName())));
    }
}
