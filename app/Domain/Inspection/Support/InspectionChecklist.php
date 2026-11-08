<?php

declare(strict_types=1);

namespace App\Domain\Inspection\Support;

use App\Domain\Applications\Enums\Service;

/**
 * The Inspector's checklist for a service, from config/inspection.php
 * (02 §4.8, 04 §6: the Operator License adds the fumigation equipment).
 */
final class InspectionChecklist
{
    /**
     * @return array<string, string> item code => English label
     */
    public static function items(Service $service): array
    {
        $items = (array) config('inspection.items.COMPANY_LICENSE', []);

        if ($service === Service::OperatorLicense) {
            $items += (array) config('inspection.items.OPERATOR_LICENSE_EXTRA', []);
        }

        return $items;
    }

    public static function maxPhotosPerItem(): int
    {
        return (int) config('inspection.max_photos_per_item', 5);
    }
}
