<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Identity\Enums\AccountStatus;
use App\Models\CompanyUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<CompanyUser>
 */
class CompanyUserFactory extends Factory
{
    protected static ?string $password;

    /**
     * An activated account (password already changed), by default.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '+25261'.fake()->numerify('#######'),
            'password' => static::$password ??= Hash::make('password'),
            'temp_password_expires_at' => null,
            'first_login' => false,
            'email_verified_at' => now(),
            'status' => AccountStatus::Active,
            'failed_login_attempts' => 0,
            'last_login_at' => now(),
            'password_changed_at' => now(),
            'preferred_locale' => 'en',
            'terms_accepted_at' => now(),
        ];
    }

    /**
     * Just signed up: temporary password, never logged in (D-063).
     */
    public function pendingFirstLogin(?string $temporaryPassword = null): static
    {
        return $this->state(fn (array $attributes) => [
            'password' => $temporaryPassword === null ? $attributes['password'] : Hash::make($temporaryPassword),
            'temp_password_expires_at' => now()->addHour(),
            'first_login' => true,
            'email_verified_at' => null,
            'last_login_at' => null,
            'password_changed_at' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AccountStatus::Inactive,
        ]);
    }

    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }
}
