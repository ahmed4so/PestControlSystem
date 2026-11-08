<?php

declare(strict_types=1);

namespace App\Domain\Inspection\Enums;

/**
 * Status of one inspection (02 §4.8).
 */
enum InspectionStatus: string
{
    case Scheduled = 'SCHEDULED';
    case Completed = 'COMPLETED';
    case Cancelled = 'CANCELLED';
}
