<?php

declare(strict_types=1);

namespace App\Domain\Inspection\Enums;

/**
 * The Inspector's overall finding; not a licensing decision (02 §4.8).
 */
enum OverallResult: string
{
    case Satisfactory = 'SATISFACTORY';
    case DeficienciesFound = 'DEFICIENCIES_FOUND';
}
