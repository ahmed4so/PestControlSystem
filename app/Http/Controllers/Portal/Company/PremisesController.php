<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal\Company;

use App\Domain\CompanyRegistry\Actions\SavePremises;
use App\Domain\CompanyRegistry\Enums\PremisesType;
use App\Domain\ReferenceData\Support\AddressOptions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\Company\PremisesRequest;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Premises;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The company's premises (02 §4.4, 05 §4.3). The facility checklist is the
 * Inspector's job, not the company's (D-192).
 */
class PremisesController extends Controller
{
    public function index(Request $request): Response
    {
        $company = $this->company($request);

        return Inertia::render('portal/premises/index', [
            'premises' => $company->premises()->orderBy('label')->get()->map(fn (Premises $premises) => [
                'id' => $premises->id,
                'label' => $premises->label,
                'type' => $premises->premises_type->label(),
                'town' => $premises->address_town_or_market,
            ]),
            'canCreate' => Gate::allows('create', [Premises::class, $company]),
        ]);
    }

    public function create(Request $request): Response
    {
        Gate::authorize('create', [Premises::class, $this->company($request)]);

        return Inertia::render('portal/premises/form', $this->formProps(null));
    }

    public function store(PremisesRequest $request, SavePremises $savePremises): RedirectResponse
    {
        $company = $this->company($request);
        Gate::authorize('create', [Premises::class, $company]);

        $premises = $savePremises($company, $request->validated());

        return redirect()->route('portal.premises.edit', $premises)->with('success', __('Saved.'));
    }

    public function edit(Premises $premises): Response
    {
        return Inertia::render('portal/premises/form', $this->formProps($premises));
    }

    public function update(PremisesRequest $request, Premises $premises, SavePremises $savePremises): RedirectResponse
    {
        Gate::authorize('update', $premises);

        $savePremises($premises->company, $request->validated(), $premises);

        return back()->with('success', __('Saved.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function formProps(?Premises $premises): array
    {
        return [
            'premises' => $premises === null ? null : [
                ...$premises->only([
                    'id', 'label', 'gps_lat', 'gps_lng', 'waste_disposal_method',
                    'address_fms_id', 'address_region_id', 'address_district', 'address_town_or_market', 'address_street', 'address_details',
                ]),
                'premises_type' => $premises->premises_type->value,
            ],
            'canEdit' => $premises === null || Gate::allows('update', $premises),
            'types' => collect(PremisesType::cases())->map(fn (PremisesType $type) => ['value' => $type->value, 'label' => $type->label()]),
            'fms' => AddressOptions::forForms(),
        ];
    }

    private function company(Request $request): Company
    {
        /** @var CompanyUser $account */
        $account = $request->user();

        return Company::query()->findOrFail($account->company_id);
    }
}
