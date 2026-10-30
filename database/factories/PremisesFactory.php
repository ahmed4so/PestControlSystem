<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\CompanyRegistry\Enums\PremisesType;
use App\Models\Company;
use App\Models\Premises;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Premises>
 */
class PremisesFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'label' => 'Main warehouse',
            'premises_type' => PremisesType::Warehouse,
            'address_town_or_market' => 'Hodan',
        ];
    }
}
