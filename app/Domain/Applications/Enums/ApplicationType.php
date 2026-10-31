<?php

declare(strict_types=1);

namespace App\Domain\Applications\Enums;

/**
 * Application types (02-data-model §4.6, 04 §8). Import Permits are NEW only.
 */
enum ApplicationType: string
{
    case New = 'NEW';
    case Renewal = 'RENEWAL';
    case Amendment = 'AMENDMENT';
}
