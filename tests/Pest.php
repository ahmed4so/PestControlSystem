<?php

use App\Domain\Identity\Enums\StaffRole;
use App\Models\CompanyUser;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Honeypot\EncryptedTime;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
| Helpers
*/

/**
 * A staff user ready to work: password set, 2FA confirmed (D-052).
 *
 * @param  list<string>  $roles
 * @param  array<string, mixed>  $attributes
 */
function staffUser(array $roles = [], array $attributes = []): User
{
    if ($roles !== []) {
        test()->seed(RolesAndPermissionsSeeder::class);
    }

    $user = User::factory()->withTwoFactor()->create($attributes);

    if ($roles !== []) {
        $user->syncRoles($roles);
    }

    return $user;
}

function systemAdministrator(): User
{
    return staffUser([StaffRole::SystemAdministrator->value]);
}

/**
 * @param  array<string, mixed>  $attributes
 */
function companyUser(array $attributes = []): CompanyUser
{
    return CompanyUser::factory()->create($attributes);
}

/**
 * Valid honeypot fields: hidden field empty, form opened long enough ago
 * (D-080).
 *
 * @return array<string, string>
 */
function honeypotFields(int $openedSecondsAgo = 10): array
{
    return [
        config('honeypot.name_field_name').'_abc123' => '',
        config('honeypot.valid_from_field_name') => EncryptedTime::create(now()->subSeconds($openedSecondsAgo - (int) config('honeypot.amount_of_seconds')))->__toString(),
    ];
}

/**
 * Session values marking the password as just confirmed (D-085).
 *
 * @return array<string, int>
 */
function passwordConfirmed(): array
{
    return ['auth.password_confirmed_at' => time()];
}
