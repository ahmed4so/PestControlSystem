<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal\Company;

use App\Domain\CompanyRegistry\Actions\CreateCompany;
use App\Domain\CompanyRegistry\Actions\UpdateCompanyProfile;
use App\Domain\CompanyRegistry\Support\CompanyEditLock;
use App\Domain\CompanyRegistry\Support\ProfileCompleteness;
use App\Domain\ReferenceData\Support\AddressOptions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\Company\StoreCompanyRequest;
use App\Http\Requests\Portal\Company\UpdateCompanyRequest;
use App\Models\Company;
use App\Models\CompanyUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The company profile (02 §4.4, 05 §4.1).
 */
class CompanyController extends Controller
{
    public function create(Request $request): Response|RedirectResponse
    {
        if ($this->account($request)->company_id !== null) {
            return redirect()->route('portal.company.edit');
        }

        return Inertia::render('portal/company/setup');
    }

    public function store(StoreCompanyRequest $request, CreateCompany $createCompany): RedirectResponse
    {
        /** @var array{name: string, business_registration_no: string} $data */
        $data = $request->validated();

        $createCompany($this->account($request), $data);

        return redirect()->route('portal.company.edit')->with('success', __('Your company profile was created. Complete the remaining details.'));
    }

    public function edit(Request $request, CompanyEditLock $lock): Response
    {
        $company = $this->company($request);

        return Inertia::render('portal/company/edit', [
            'company' => $company->only([
                'id', 'name', 'business_registration_no', 'tax_compliance_no', 'email', 'phone', 'owner_person_id',
                'address_fms_id', 'address_region_id', 'address_district', 'address_town_or_market', 'address_street', 'address_details',
            ]),
            'persons' => $company->persons()->whereNull('released_at')->orderBy('full_name')->get(['id', 'full_name']),
            'missing' => ProfileCompleteness::missing($company),
            'complete' => $company->profile_completed_at !== null,
            'amendmentRequired' => $company->amendment_required_at !== null,
            'canEdit' => $lock->canEditProfile($company),
            'fms' => AddressOptions::forForms(),
        ]);
    }

    public function update(UpdateCompanyRequest $request, UpdateCompanyProfile $updateProfile): RedirectResponse
    {
        $company = $this->company($request);
        Gate::authorize('update', $company);

        $updateProfile($company, $request->validated());

        return back()->with('success', __('Saved.'));
    }

    private function account(Request $request): CompanyUser
    {
        /** @var CompanyUser */
        return $request->user();
    }

    private function company(Request $request): Company
    {
        return Company::query()->findOrFail($this->account($request)->company_id);
    }
}
