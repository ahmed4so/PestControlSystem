<?php

declare(strict_types=1);

namespace App\Domain\Identity\Enums;

/**
 * Account status for staff users and company users (02-data-model §4.1, D-053).
 */
enum AccountStatus: string
{
    case Active = 'ACTIVE';
    case Inactive = 'INACTIVE';
}
