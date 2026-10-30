<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal\Auth;

use App\Domain\Identity\Actions\RegisterCompanyUser;
use App\Domain\Identity\Support\PublicFormMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\Auth\SignUpRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Honeypot\Honeypot;

/**
 * Company sign-up (03 §4, D-063). Every valid submission gets the same
 * answer, whether or not the email has an account.
 */
class SignUpController extends Controller
{
    public function create(Request $request, Honeypot $honeypot): Response
    {
        return Inertia::render('portal/auth/signup', [
            'status' => $request->session()->get('status'),
            'honeypot' => $honeypot->toArray(),
        ]);
    }

    public function store(SignUpRequest $request, RegisterCompanyUser $register): RedirectResponse
    {
        /** @var array{full_name: string, email: string, phone: string} $data */
        $data = $request->safe()->only(['full_name', 'email', 'phone']);

        $register($data, app()->getLocale());

        return back()->with('status', __(PublicFormMessages::SIGN_UP));
    }
}
