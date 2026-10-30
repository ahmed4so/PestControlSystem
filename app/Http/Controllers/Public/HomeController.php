<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Public landing page with links to the company and staff logins (D-166).
 */
class HomeController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('public/home');
    }
}
