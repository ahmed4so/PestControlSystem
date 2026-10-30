<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal\Company;

use App\Domain\CompanyRegistry\Actions\SavePerson;
use App\Domain\CompanyRegistry\Enums\DocumentType;
use App\Domain\ReferenceData\Support\AddressOptions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\Company\PersonRequest;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Document;
use App\Models\Person;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Persons of the company (02 §4.4, 05 §4.2). Another company's person gives
 * 404 through the company scope (03 §11.3).
 */
class PersonController extends Controller
{
    public function index(Request $request): Response
    {
        $company = $this->company($request);

        return Inertia::render('portal/persons/index', [
            'persons' => $company->persons()->orderBy('full_name')->get()->map(fn (Person $person) => [
                'id' => $person->id,
                'full_name' => $person->full_name,
                'national_id' => $person->maskedNationalId(),
                'is_owner' => $company->owner_person_id === $person->id,
                'released' => $person->isReleased(),
            ]),
            'canCreate' => Gate::allows('create', [Person::class, $company]),
        ]);
    }

    public function create(Request $request): Response
    {
        Gate::authorize('create', [Person::class, $this->company($request)]);

        return Inertia::render('portal/persons/form', $this->formProps(null));
    }

    public function store(PersonRequest $request, SavePerson $savePerson): RedirectResponse
    {
        $company = $this->company($request);
        Gate::authorize('create', [Person::class, $company]);

        $person = $savePerson($company, $request->validated());

        return redirect()->route('portal.persons.edit', $person)->with('success', __('Saved.'));
    }

    public function edit(Person $person): Response
    {
        return Inertia::render('portal/persons/form', $this->formProps($person));
    }

    public function update(PersonRequest $request, Person $person, SavePerson $savePerson): RedirectResponse
    {
        Gate::authorize('update', $person);

        $savePerson($person->company, $request->validated(), $person);

        return back()->with('success', __('Saved.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function formProps(?Person $person): array
    {
        return [
            'person' => $person === null ? null : [
                ...$person->only([
                    'id', 'full_name', 'nationality', 'email', 'phone', 'qualification', 'institution',
                    'years_of_experience', 'training_pcp_handling', 'training_fumigation',
                    'address_fms_id', 'address_region_id', 'address_district', 'address_town_or_market', 'address_street', 'address_details',
                ]),
                // The company already holds the full ID; it is shown to it again.
                'national_id_no' => $person->national_id_no,
                'released' => $person->isReleased(),
                'documents' => $person->documents()->whereNull('replaced_by_id')->get()->map(fn (Document $document) => [
                    'id' => $document->id,
                    'type' => $document->document_type->value,
                    'label' => $document->document_type->label(),
                    'uploaded_at' => $document->created_at?->toIso8601String(),
                ]),
            ],
            'canEdit' => $person === null || Gate::allows('update', $person),
            'personDocumentTypes' => collect(DocumentType::cases())
                ->filter(fn (DocumentType $type) => $type->belongsToPerson())
                ->map(fn (DocumentType $type) => ['value' => $type->value, 'label' => $type->label()])
                ->values(),
            'fms' => AddressOptions::forForms(),
            'countries' => AddressOptions::countries(),
        ];
    }

    private function company(Request $request): Company
    {
        /** @var CompanyUser $account */
        $account = $request->user();

        return Company::query()->findOrFail($account->company_id);
    }
}
