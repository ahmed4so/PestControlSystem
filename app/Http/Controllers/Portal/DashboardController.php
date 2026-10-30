<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Domain\CompanyRegistry\Support\ProfileCompleteness;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyUser;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The company dashboard: profile completeness for now (D-178);
 * applications arrive in Phase 4.
 */
class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        /** @var CompanyUser $account */
        $account = $request->user();
        $company = Company::query()->findOrFail($account->company_id);

        return Inertia::render('portal/dashboard', [
            'companyName' => $company->name,
            'missing' => ProfileCompleteness::missing($company),
            'amendmentRequired' => $company->amendment_required_at !== null,
        ]);
    }
}
