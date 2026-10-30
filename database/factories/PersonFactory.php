<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\CompanyRegistry\Support\NationalIdHasher;
use App\Models\Company;
use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Person>
 */
class PersonFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'full_name' => fake()->name(),
            'nationality' => 'SO',
            'national_id_no' => fake()->unique()->numerify('###########'),
            'email' => fake()->safeEmail(),
            'phone' => '+25261'.fake()->numerify('#######'),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Person $person): void {
            $person->national_id_hash = NationalIdHasher::hash($person->national_id_no);
        });
    }

    public function withNationalId(string $nationalId): static
    {
        return $this->state(fn (array $attributes) => ['national_id_no' => $nationalId]);
    }
}
