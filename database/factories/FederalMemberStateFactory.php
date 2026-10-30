<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FederalMemberState;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FederalMemberState>
 */
class FederalMemberStateFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->city();

        return [
            'code' => strtoupper(fake()->unique()->lexify('FMS???')),
            'name_en' => $name,
            'name_so' => $name,
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
