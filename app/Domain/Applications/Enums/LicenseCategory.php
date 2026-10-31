<?php

declare(strict_types=1);

namespace App\Domain\Applications\Enums;

/**
 * Company License categories (D-014, 02-data-model §4.6).
 */
enum LicenseCategory: string
{
    case Importer = 'IMPORTER';
    case Wholesaler = 'WHOLESALER';
    case Retailer = 'RETAILER';
}
