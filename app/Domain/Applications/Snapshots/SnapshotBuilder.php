<?php

declare(strict_types=1);

namespace App\Domain\Applications\Snapshots;

use App\Models\Application;
use App\Models\Company;
use App\Models\Document;
use App\Models\FederalMemberState;
use App\Models\Person;
use App\Models\Premises;
use App\Models\Region;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * Copies everything an application references into a plain array at
 * submission (02-data-model §6.3, D-042, D-069, D-070). Files are
 * referenced by `stored_file_id`, never copied. National IDs stay as the
 * encrypted value stored in the database. The facility checklist is not
 * included: it is the Inspector's job (D-192).
 */
final class SnapshotBuilder
{
    public const SCHEMA_VERSION = 1;

    /**
     * @return array<string, mixed>
     */
    public function build(Application $application): array
    {
        $company = Company::query()->withoutGlobalScope('company')->findOrFail($application->company_id);
        $owner = $company->owner_person_id === null
            ? null
            : Person::query()->withoutGlobalScope('company')->find($company->owner_person_id);

        $technicalPersons = $application->technicalPersons()->withoutGlobalScope('company')->orderBy('persons.id')->get();
        $premises = $application->premises()->withoutGlobalScope('company')->orderBy('premises.id')->get();

        return [
            'schema_version' => self::SCHEMA_VERSION,
            'application' => [
                'id' => $application->id,
                'service' => $application->service->value,
                'license_category' => $application->license_category?->value,
                'application_type' => $application->application_type->value,
                'previous_license_id' => $application->previous_license_id,
            ],
            'company' => [
                'id' => $company->id,
                'name' => $company->name,
                'business_registration_no' => $company->business_registration_no,
                'tax_compliance_no' => $company->tax_compliance_no,
                'email' => $company->email,
                'phone' => $company->phone,
                'address' => $this->address($company),
            ],
            'owner' => $owner === null ? null : $this->person($owner, withQualifications: false),
            'technical_persons' => $technicalPersons->map(fn (Person $person) => $this->person($person, withQualifications: true))->values()->all(),
            'premises' => $premises->map(fn (Premises $item) => [
                'id' => $item->id,
                'label' => $item->label,
                'premises_type' => $item->premises_type->value,
                'address' => $this->address($item),
                'gps_lat' => $item->gps_lat,
                'gps_lng' => $item->gps_lng,
                'waste_disposal_method' => $item->waste_disposal_method,
            ])->values()->all(),
            'documents' => $this->documents($company->id, personId: null),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function person(Person $person, bool $withQualifications): array
    {
        $data = [
            'id' => $person->id,
            'full_name' => $person->full_name,
            'nationality' => $person->nationality,
            'national_id_encrypted' => $person->getRawOriginal('national_id_no'),
            'email' => $person->email,
            'phone' => $person->phone,
            'address' => $this->address($person),
        ];

        if ($withQualifications) {
            $data += [
                'qualification' => $person->qualification,
                'institution' => $person->institution,
                'years_of_experience' => $person->years_of_experience,
                'training_pcp_handling' => $person->training_pcp_handling,
                'training_fumigation' => $person->training_fumigation,
                'documents' => $this->documents($person->company_id, personId: $person->id),
            ];
        }

        return $data;
    }

    /**
     * Current documents (company documents, or one person's), ordered by type.
     *
     * @return list<array<string, mixed>>
     */
    private function documents(int $companyId, ?int $personId): array
    {
        return Document::query()->withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->where('person_id', $personId)
            ->whereNull('replaced_by_id')
            ->orderBy('document_type')
            ->orderBy('id')
            ->get()
            ->map(fn (Document $document) => [
                'id' => $document->id,
                'document_type' => $document->document_type->value,
                'label' => $document->document_type->label(),
                'document_number' => $document->document_number,
                'issued_on' => self::date($document->issued_on),
                'expires_on' => self::date($document->expires_on),
                'stored_file_id' => $document->stored_file_id,
            ])
            ->values()
            ->all();
    }

    /**
     * The address with the list names as they were at submission (D-065).
     *
     * @return array<string, mixed>
     */
    private function address(Model $model): array
    {
        $fms = FederalMemberState::query()->withTrashed()->find($model->getAttribute('address_fms_id'));
        $region = Region::query()->withTrashed()->find($model->getAttribute('address_region_id'));

        return [
            'fms_id' => $fms?->id,
            'fms_name_en' => $fms?->name_en,
            'fms_name_so' => $fms?->name_so,
            'region_id' => $region?->id,
            'region_name_en' => $region?->name_en,
            'region_name_so' => $region?->name_so,
            'district' => $model->getAttribute('address_district'),
            'town_or_market' => $model->getAttribute('address_town_or_market'),
            'street' => $model->getAttribute('address_street'),
            'details' => $model->getAttribute('address_details'),
        ];
    }

    private static function date(?CarbonInterface $date): ?string
    {
        return $date?->format('Y-m-d');
    }
}
