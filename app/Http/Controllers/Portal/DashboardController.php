<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The company dashboard; empty until the company profile arrives (Phase 2).
 */
class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('portal/dashboard');
    }
}
