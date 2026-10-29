<?php

declare(strict_types=1);

namespace App\Domain\ReferenceData\Enums;

/**
 * Number prefixes and their digit counts (D-011, D-075, 02-data-model §7).
 */
enum NumberPrefix: string
{
    // Licenses and permits: PREFIX-YYYY-NNNN (D-011).
    case ImportationLicense = 'PCPIL';
    case WholesaleLicense = 'PCPWS';
    case RetailerLicense = 'PCPRT';
    case OperatorLicense = 'PCA';
    case ImportPermit = 'PCPIP';

    // Application references: CL / OL / IP-YYYY-NNNNN (D-075).
    case CompanyLicenseApplication = 'CL';
    case OperatorLicenseApplication = 'OL';
    case ImportPermitApplication = 'IP';

    // Permission-to-pay letters and account recovery requests: PREFIX-YYYY-NNNN (D-075).
    case PermissionToPayLetter = 'PTP';
    case AccountRecoveryRequest = 'AR';

    public function digits(): int
    {
        return match ($this) {
            self::CompanyLicenseApplication,
            self::OperatorLicenseApplication,
            self::ImportPermitApplication => 5,
            default => 4,
        };
    }

    public function format(int $year, int $number): string
    {
        return sprintf('%s-%04d-%s', $this->value, $year, str_pad((string) $number, $this->digits(), '0', STR_PAD_LEFT));
    }
}
