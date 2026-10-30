<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Middleware\SetLocale;
use App\Models\CompanyUser;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Language switch (D-032, D-169): a cookie for guests, and the preferred
 * language of a logged-in company account.
 */
class LocaleController extends Controller
{
    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        $user = $request->user();

        if ($user instanceof CompanyUser || $user instanceof User) {
            $user->update(['preferred_locale' => $locale]);
        }

        return back()->withCookie(cookie()->forever(SetLocale::COOKIE, $locale));
    }
}
