<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal\Auth;

use App\Domain\Identity\Actions\IssueTemporaryPassword;
use App\Domain\Identity\Support\PublicFormMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\Auth\TemporaryPasswordRequest;
use App\Models\CompanyUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Honeypot\Honeypot;

/**
 * "Send me a new temporary password" (03 §4): only for accounts that have
 * never logged in; the previous temporary password stops working. Same
 * answer in every case.
 */
class TemporaryPasswordController extends Controller
{
    public function create(Request $request, Honeypot $honeypot): Response
    {
        return Inertia::render('portal/auth/temporary-password', [
            'status' => $request->session()->get('status'),
            'honeypot' => $honeypot->toArray(),
        ]);
    }

    public function store(TemporaryPasswordRequest $request, IssueTemporaryPassword $issue): RedirectResponse
    {
        $account = CompanyUser::query()
            ->where('email', $request->validated('email'))
            ->whereNull('last_login_at')
            ->where('first_login', true)
            ->first();

        if ($account !== null && $account->isActive()) {
            $issue($account);
        }

        return back()->with('status', __(PublicFormMessages::TEMPORARY_PASSWORD));
    }
}
