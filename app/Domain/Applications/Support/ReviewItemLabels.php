<?php

declare(strict_types=1);

namespace App\Domain\Applications\Support;

/**
 * English names of the review items of 04 §5.3 and §7, translated with
 * __(). The item lists themselves are configuration (D-102); an unknown
 * code is shown as is.
 */
final class ReviewItemLabels
{
    private const LABELS = [
        // ADMIN_REVIEW (procedure 6.3)
        'COMMERCE_REGISTRATION' => 'Commerce registration',
        'TAX_COMPLIANCE' => 'Tax compliance',
        'IDENTIFICATION' => 'Identification',
        'COMPANY_PROFILE' => 'Company profile',
        'CATEGORY' => 'License category',
        'APPLICATION_COMPLETENESS' => 'Application completeness',
        'SUPPORTING_DOCUMENTS' => 'Supporting documents',
        'IMPORTATION_LICENSE' => 'Importation license',
        // TECHNICAL_DOC_REVIEW (6.4)
        'TECHNICAL_QUALIFICATION' => 'Technical qualification',
        'TECHNICAL_CV' => 'Technical person CV',
        'PRODUCT_REGISTRATION' => 'Product registration',
        'PRODUCT_LABEL' => 'Product label',
        'REGISTERED_AND_INTENDED_USE' => 'Registered and intended use',
        'QUANTITY_AND_PURPOSE' => 'Quantity and purpose',
        'ENTRY_DETAILS' => 'Entry details',
        // CONSOLIDATED_REVIEW (6.8)
        'DOCUMENTS_CONSISTENT' => 'Documents consistent',
        'INSPECTION_REPORT_PRESENT' => 'Inspection report present',
        'INSPECTION_FINDINGS_ADDRESSED' => 'Inspection findings addressed',
        'OVERALL_COMPLIANCE' => 'Overall compliance',
    ];

    public static function label(string $code): string
    {
        return self::LABELS[$code] ?? $code;
    }
}
