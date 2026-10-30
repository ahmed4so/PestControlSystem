<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FederalMemberState;
use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Region>
 */
class RegionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->city();

        return [
            'federal_member_state_id' => FederalMemberState::factory(),
            'name_en' => $name,
            'name_so' => $name,
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
