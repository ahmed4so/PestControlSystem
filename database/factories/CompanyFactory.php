<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Company;
use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'business_registration_no' => strtoupper(fake()->unique()->bothify('BR-#####-??')),
            'tax_compliance_no' => strtoupper(fake()->bothify('TX-######')),
            'email' => fake()->unique()->companyEmail(),
            'phone' => '+25261'.fake()->numerify('#######'),
        ];
    }

    /**
     * With a complete address in a new FMS and region.
     */
    public function withAddress(): static
    {
        return $this->state(function (array $attributes): array {
            $region = Region::factory()->create();

            return [
                'address_fms_id' => $region->federal_member_state_id,
                'address_region_id' => $region->id,
                'address_town_or_market' => 'Hodan',
            ];
        });
    }
}
