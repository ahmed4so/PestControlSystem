<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Notifications\Enums\NotificationCode;
use App\Domain\Notifications\Enums\NotificationStatus;
use App\Models\CompanyUser;
use App\Models\NotificationLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationLog>
 */
class NotificationLogFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'recipient_type' => CompanyUser::class,
            'recipient_id' => CompanyUser::factory(),
            'email' => fake()->safeEmail(),
            'template' => NotificationCode::PasswordChanged,
            'locale' => 'en',
            'status' => NotificationStatus::Queued,
        ];
    }

    public function failed(string $error = 'Connection refused'): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => NotificationStatus::Failed,
            'error' => $error,
        ]);
    }
}
