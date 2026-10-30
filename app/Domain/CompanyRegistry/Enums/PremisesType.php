<?php

declare(strict_types=1);

namespace App\Domain\CompanyRegistry\Enums;

/**
 * Premises types (02 §4.4).
 */
enum PremisesType: string
{
    case Warehouse = 'WAREHOUSE';
    case Shop = 'SHOP';
    case Store = 'STORE';
    case Office = 'OFFICE';

    public function label(): string
    {
        return match ($this) {
            self::Warehouse => 'Warehouse',
            self::Shop => 'Shop',
            self::Store => 'Store',
            self::Office => 'Office',
        };
    }
}
