<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\CompanyRegistry\Enums\ChecklistItem;
use App\Models\FacilityChecklistItem;
use App\Models\Premises;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FacilityChecklistItem>
 */
class FacilityChecklistItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'premises_id' => Premises::factory(),
            'item_code' => ChecklistItem::Ventilation,
            'is_available' => true,
        ];
    }
}
