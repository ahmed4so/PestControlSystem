<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal\Company;

use App\Domain\CompanyRegistry\Actions\AddChecklistPhoto;
use App\Domain\CompanyRegistry\Actions\SavePremises;
use App\Domain\CompanyRegistry\Enums\ChecklistItem;
use App\Domain\CompanyRegistry\Enums\PremisesType;
use App\Domain\Documents\Exceptions\UnsafeUpload;
use App\Domain\ReferenceData\Support\AddressOptions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\Company\ChecklistItemRequest;
use App\Http\Requests\Portal\Company\ChecklistPhotoRequest;
use App\Http\Requests\Portal\Company\PremisesRequest;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\FacilityChecklistItem;
use App\Models\FacilityChecklistPhoto;
use App\Models\Premises;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Premises with the facility checklist and photos (02 §4.4, 05 §4.3,
 * D-018, D-135).
 */
class PremisesController extends Controller
{
    public function index(Request $request): Response
    {
        $company = $this->company($request);

        return Inertia::render('portal/premises/index', [
            'premises' => $company->premises()->withCount(['checklistItems as available_count' => fn ($query) => $query->where('is_available', true)])
                ->orderBy('label')->get()->map(fn (Premises $premises) => [
                    'id' => $premises->id,
                    'label' => $premises->label,
                    'type' => $premises->premises_type->label(),
                    'available_count' => $premises->getAttribute('available_count'),
                ]),
            'itemCount' => count(ChecklistItem::cases()),
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

        return redirect()->route('portal.premises.edit', $premises)->with('success', __('Saved. Now complete the facility checklist.'));
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

    public function updateItem(ChecklistItemRequest $request, Premises $premises, FacilityChecklistItem $item): RedirectResponse
    {
        Gate::authorize('update', $premises);

        $item->update($request->validated());

        return back()->with('success', __('Saved.'));
    }

    public function storePhoto(ChecklistPhotoRequest $request, Premises $premises, FacilityChecklistItem $item, AddChecklistPhoto $addPhoto): RedirectResponse
    {
        Gate::authorize('update', $premises);

        /** @var CompanyUser $account */
        $account = $request->user();
        $capturedAt = $request->filled('captured_at') ? CarbonImmutable::parse((string) $request->input('captured_at')) : null;

        try {
            $addPhoto($item, $request->file('photo'), $capturedAt, $account);
        } catch (UnsafeUpload $exception) {
            throw ValidationException::withMessages(['photo' => __($exception->getMessage())]);
        }

        return back()->with('success', __('Photo added.'));
    }

    public function destroyPhoto(Premises $premises, FacilityChecklistPhoto $photo): RedirectResponse
    {
        Gate::authorize('update', $premises);

        $photo->delete();

        return back()->with('success', __('Photo removed.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function formProps(?Premises $premises): array
    {
        $premises?->load(['checklistItems.photos']);

        return [
            'premises' => $premises === null ? null : [
                ...$premises->only([
                    'id', 'label', 'gps_lat', 'gps_lng', 'waste_disposal_method',
                    'address_fms_id', 'address_region_id', 'address_district', 'address_town_or_market', 'address_street', 'address_details',
                ]),
                'premises_type' => $premises->premises_type->value,
                'checklist' => $premises->checklistItems
                    ->sortBy(fn (FacilityChecklistItem $item) => array_search($item->item_code, ChecklistItem::cases(), true))
                    ->values()
                    ->map(fn (FacilityChecklistItem $item) => [
                        'id' => $item->id,
                        'code' => $item->item_code->value,
                        'label' => $item->item_code->label(),
                        'is_available' => $item->is_available,
                        'notes' => $item->notes,
                        'photos' => $item->photos->map(fn (FacilityChecklistPhoto $photo) => [
                            'id' => $photo->id,
                            'url' => route('portal.files.photo', $photo),
                        ])->values(),
                    ]),
            ],
            'canEdit' => $premises === null || Gate::allows('update', $premises),
            'types' => collect(PremisesType::cases())->map(fn (PremisesType $type) => ['value' => $type->value, 'label' => $type->label()]),
            'maxPhotos' => ChecklistItem::MAX_PHOTOS,
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
