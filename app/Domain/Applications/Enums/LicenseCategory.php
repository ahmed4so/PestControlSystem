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

    /**
     * English name, translated with __() (D-014).
     */
    public function label(): string
    {
        return match ($this) {
            self::Importer => 'Importer',
            self::Wholesaler => 'Wholesaler',
            self::Retailer => 'Retailer',
        };
    }
}
