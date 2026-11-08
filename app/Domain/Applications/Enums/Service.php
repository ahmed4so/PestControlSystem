<?php

declare(strict_types=1);

namespace App\Domain\Applications\Enums;

use App\Domain\ReferenceData\Enums\NumberPrefix;

/**
 * The three Phase 1 services (02-data-model §4.6).
 */
enum Service: string
{
    case CompanyLicense = 'COMPANY_LICENSE';
    case OperatorLicense = 'OPERATOR_LICENSE';
    case ImportPermit = 'IMPORT_PERMIT';

    /**
     * English name, translated with __().
     */
    public function label(): string
    {
        return match ($this) {
            self::CompanyLicense => 'Pest Control Products Company License',
            self::OperatorLicense => 'Pest Control Products Operator License',
            self::ImportPermit => 'Import Permit of Pest Control Products',
        };
    }

    /**
     * Application reference prefix: CL, OL, IP (D-075).
     */
    public function referencePrefix(): NumberPrefix
    {
        return match ($this) {
            self::CompanyLicense => NumberPrefix::CompanyLicenseApplication,
            self::OperatorLicense => NumberPrefix::OperatorLicenseApplication,
            self::ImportPermit => NumberPrefix::ImportPermitApplication,
        };
    }

    /**
     * The one-open-application key (02 §6.5, D-044, D-072). Import Permits
     * have none: any number may be open.
     */
    public function openKey(?LicenseCategory $category): ?string
    {
        return match ($this) {
            self::CompanyLicense => 'COMPANY_LICENSE:'.($category->value ?? ''),
            self::OperatorLicense => 'OPERATOR_LICENSE',
            self::ImportPermit => null,
        };
    }
}
