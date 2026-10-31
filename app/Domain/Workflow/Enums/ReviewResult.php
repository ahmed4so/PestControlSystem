<?php

declare(strict_types=1);

namespace App\Domain\Workflow\Enums;

/**
 * Review item results (02-data-model §4.7, 04 §5.3).
 */
enum ReviewResult: string
{
    case Compliant = 'COMPLIANT';
    case Deficient = 'DEFICIENT';
    case ClarificationRequired = 'CLARIFICATION_REQUIRED';
}
