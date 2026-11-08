<?php

declare(strict_types=1);

namespace App\Domain\Inspection\Enums;

/**
 * Result of one checklist item (05 §6).
 */
enum InspectionItemResult: string
{
    case Compliant = 'COMPLIANT';
    case NonCompliant = 'NON_COMPLIANT';
    case NotApplicable = 'NOT_APPLICABLE';
}
