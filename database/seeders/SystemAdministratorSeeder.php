<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Identity\Enums\AccountStatus;
use App\Domain\Identity\Enums\StaffRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class SystemAdministratorSeeder extends Seeder
{
    /**
     * System Administrator account for local development only.
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            return;
        }

        $user = User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'System Administrator',
                'password' => 'password',
                'status' => AccountStatus::Active,
                'first_login' => false,
                'email_verified_at' => now(),
                'password_changed_at' => now(),
            ]
        );

        $user->syncRoles([StaffRole::SystemAdministrator->value]);
    }
}
