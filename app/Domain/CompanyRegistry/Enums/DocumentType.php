<?php

declare(strict_types=1);

namespace App\Domain\CompanyRegistry\Enums;

/**
 * Company document types (02 §4.4, 05 §4.4).
 */
enum DocumentType: string
{
    case CommerceRegistrationCertificate = 'COMMERCE_REGISTRATION_CERTIFICATE';
    case TaxComplianceCertificate = 'TAX_COMPLIANCE_CERTIFICATE';
    case MdNationalId = 'MD_NATIONAL_ID';
    case CompanyProfile = 'COMPANY_PROFILE';
    case DegreeCertificate = 'DEGREE_CERTIFICATE';
    case Cv = 'CV';
    case EquipmentInventory = 'EQUIPMENT_INVENTORY';

    public function label(): string
    {
        return match ($this) {
            self::CommerceRegistrationCertificate => 'Commerce registration certificate',
            self::TaxComplianceCertificate => 'Tax compliance certificate',
            self::MdNationalId => 'MD national ID copy',
            self::CompanyProfile => 'Company profile',
            self::DegreeCertificate => 'Degree certificate',
            self::Cv => 'CV',
            self::EquipmentInventory => 'Equipment inventory',
        };
    }

    /**
     * Certificates with a number, issue and expiry date (05 §4.4, D-017).
     * They can be renewed even while the profile is locked (D-073).
     */
    public function isCertificate(): bool
    {
        return in_array($this, [self::CommerceRegistrationCertificate, self::TaxComplianceCertificate], true);
    }

    /**
     * Documents that belong to a person (02 §4.4).
     */
    public function belongsToPerson(): bool
    {
        return in_array($this, [self::DegreeCertificate, self::Cv], true);
    }
}
