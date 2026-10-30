<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\CompanyRegistry\Enums\ChecklistItem;
use App\Domain\CompanyRegistry\Support\ProfileCompleteness;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Document;
use App\Models\FacilityChecklistItem;
use App\Models\FacilityChecklistPhoto;
use App\Models\Person;
use App\Models\Premises;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Read-only company directory and company file for staff (09 Phase 2,
 * D-177). National IDs are always masked (D-181); files open through the
 * audited download.
 */
class CompanyController extends Controller implements HasMiddleware
{
    /**
     * @return list<Middleware>
     */
    public static function middleware(): array
    {
        return [new Middleware('permission:view applications')];
    }

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));

        $companies = Company::query()
            ->when($search !== '', fn ($query) => $query->where(fn ($q) => $q
                ->where('name', 'like', '%'.$search.'%')
                ->orWhere('business_registration_no', 'like', '%'.strtoupper($search).'%')))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString()
            ->through(fn (Company $company) => [
                'id' => $company->id,
                'name' => $company->name,
                'business_registration_no' => $company->business_registration_no,
                'complete' => $company->profile_completed_at !== null,
                'amendment_required' => $company->amendment_required_at !== null,
            ]);

        return Inertia::render('admin/companies/index', ['companies' => $companies, 'search' => $search]);
    }

    public function show(Company $company): Response
    {
        $company->load(['addressFms', 'addressRegion', 'account', 'persons', 'premises.checklistItems.photos', 'documents.person']);
        $somali = app()->getLocale() === 'so';

        return Inertia::render('admin/companies/show', [
            'company' => [
                ...$company->only(['id', 'name', 'business_registration_no', 'tax_compliance_no', 'email', 'phone',
                    'address_district', 'address_town_or_market', 'address_street', 'address_details']),
                'fms' => $somali ? $company->addressFms?->name_so : $company->addressFms?->name_en,
                'region' => $somali ? $company->addressRegion?->name_so : $company->addressRegion?->name_en,
                'account_email' => $company->account?->email,
                'complete' => $company->profile_completed_at !== null,
                'missing' => ProfileCompleteness::missing($company),
                'amendment_required_at' => $company->amendment_required_at?->toIso8601String(),
            ],
            'persons' => $company->persons->map(fn (Person $person) => [
                'id' => $person->id,
                'full_name' => $person->full_name,
                'national_id' => $person->maskedNationalId(),
                'nationality' => $person->nationality,
                'email' => $person->email,
                'phone' => $person->phone,
                'qualification' => $person->qualification,
                'is_owner' => $company->owner_person_id === $person->id,
                'released_at' => $person->released_at?->toIso8601String(),
            ])->values(),
            'premises' => $company->premises->map(fn (Premises $premises) => [
                'id' => $premises->id,
                'label' => $premises->label,
                'type' => $premises->premises_type->label(),
                'town' => $premises->address_town_or_market,
                'checklist' => $premises->checklistItems
                    ->sortBy(fn (FacilityChecklistItem $item) => array_search($item->item_code, ChecklistItem::cases(), true))
                    ->values()
                    ->map(fn (FacilityChecklistItem $item) => [
                        'label' => $item->item_code->label(),
                        'is_available' => $item->is_available,
                        'notes' => $item->notes,
                        'photos' => $item->photos->map(fn (FacilityChecklistPhoto $photo) => route('admin.files.photo', $photo))->values(),
                    ]),
            ])->values(),
            'documents' => $company->documents->sortByDesc('id')->values()->map(fn (Document $document) => [
                'id' => $document->id,
                'label' => $document->document_type->label(),
                'person' => $document->person?->full_name,
                'document_number' => $document->document_number,
                'issued_on' => $document->issued_on?->toDateString(),
                'expires_on' => $document->expires_on?->toDateString(),
                'current' => $document->isCurrent(),
                'uploaded_at' => $document->created_at?->toIso8601String(),
                'url' => route('admin.files.document', $document),
            ]),
        ]);
    }
}
