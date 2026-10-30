<?php

namespace Database\Factories;

use App\Domain\Identity\Enums\AccountStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => null,
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'status' => AccountStatus::Active,
            'first_login' => false,
            'failed_login_attempts' => 0,
            'password_changed_at' => now(),
            'preferred_locale' => 'en',
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * A new staff account waiting for its first login (D-083).
     */
    public function pendingFirstLogin(?string $temporaryPassword = null): static
    {
        return $this->state(fn (array $attributes) => [
            'password' => $temporaryPassword === null ? $attributes['password'] : Hash::make($temporaryPassword),
            'temp_password_expires_at' => now()->addDay(),
            'first_login' => true,
            'email_verified_at' => null,
            'password_changed_at' => null,
        ]);
    }

    /**
     * Indicate that the account has been deactivated (D-053).
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AccountStatus::Inactive,
        ]);
    }

    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }
}
