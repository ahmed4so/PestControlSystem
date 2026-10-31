<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Workflow\Enums\ReviewResult;
use App\Models\ReviewItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReviewItem>
 */
class ReviewItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'stage' => 'ADMIN_REVIEW',
            'item_code' => 'COMMERCE_REGISTRATION',
            'result' => ReviewResult::Compliant,
            'reviewed_by' => User::factory(),
            'reviewed_at' => now(),
        ];
    }
}
