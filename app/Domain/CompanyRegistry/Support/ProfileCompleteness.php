<?php

declare(strict_types=1);

namespace App\Domain\CompanyRegistry\Support;

use App\Domain\CompanyRegistry\Enums\DocumentType;
use App\Models\Company;
use App\Models\Document;
use App\Models\Person;

/**
 * What a complete profile needs (D-178). Premises and technical persons
 * are checked per application (Phase 4).
 */
final class ProfileCompleteness
{
    public const REQUIRED_DOCUMENTS = [
        DocumentType::CommerceRegistrationCertificate,
        DocumentType::TaxComplianceCertificate,
        DocumentType::MdNationalId,
        DocumentType::CompanyProfile,
    ];

    /**
     * The missing items, as English keys for t() (empty when complete).
     *
     * @return list<string>
     */
    public static function missing(Company $company): array
    {
        $missing = [];

        foreach (['name', 'business_registration_no', 'tax_compliance_no', 'email', 'phone'] as $field) {
            if (blank($company->getAttribute($field))) {
                $missing[] = 'Company details';
                break;
            }
        }

        if (! $company->address()->isComplete()) {
            $missing[] = 'Company address';
        }

        $owner = $company->owner_person_id === null ? null : Person::query()->withoutGlobalScopes()->find($company->owner_person_id);

        if (! $owner instanceof Person || $owner->isReleased() || ! self::ownerIsComplete($owner)) {
            $missing[] = 'Owner / Managing Director';
        }

        $current = Document::query()->withoutGlobalScopes()
            ->where('company_id', $company->id)
            ->whereNull('replaced_by_id')
            ->get()
            ->keyBy(fn (Document $document) => $document->document_type->value);

        foreach (self::REQUIRED_DOCUMENTS as $type) {
            $document = $current->get($type->value);

            if (! $document instanceof Document) {
                $missing[] = $type->label();

                continue;
            }

            if ($type->isCertificate() && (blank($document->document_number) || $document->issued_on === null || $document->expires_on === null)) {
                $missing[] = $type->label();
            }
        }

        return $missing;
    }

    /**
     * Sets or clears profile_completed_at.
     */
    public static function refresh(Company $company): void
    {
        $complete = self::missing($company) === [];

        if ($complete && $company->profile_completed_at === null) {
            $company->forceFill(['profile_completed_at' => now()])->saveQuietly();
        } elseif (! $complete && $company->profile_completed_at !== null) {
            $company->forceFill(['profile_completed_at' => null])->saveQuietly();
        }
    }

    private static function ownerIsComplete(Person $owner): bool
    {
        return filled($owner->nationality)
            && filled($owner->email)
            && filled($owner->phone)
            && $owner->address()->isComplete();
    }
}
