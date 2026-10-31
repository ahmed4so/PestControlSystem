<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Delegation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Delegation>
 */
class DelegationFactory extends Factory
{
    /**
     * In force from yesterday to next week.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'delegator_id' => User::factory(),
            'delegate_id' => User::factory(),
            'starts_on' => now()->subDay()->toDateString(),
            'ends_on' => now()->addWeek()->toDateString(),
            'reason' => 'DG on leave',
            'created_by' => fn (array $attributes) => $attributes['delegator_id'],
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'starts_on' => now()->subMonth()->toDateString(),
            'ends_on' => now()->subDays(2)->toDateString(),
        ]);
    }

    public function revoked(): static
    {
        return $this->state(fn (array $attributes) => [
            'revoked_at' => now()->subHour(),
            'revoked_by' => $attributes['delegator_id'],
        ]);
    }
}
