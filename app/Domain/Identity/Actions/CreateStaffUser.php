<?php

declare(strict_types=1);

namespace App\Domain\Identity\Actions;

use App\Domain\Identity\Enums\AccountStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Creates a staff account (03 §5.2, D-083). The System Administrator never
 * sets or sees a password: the new user gets an emailed temporary password
 * valid 24 hours, must change it, then must set up 2FA.
 */
final class CreateStaffUser
{
    public function __construct(private readonly IssueTemporaryPassword $issueTemporaryPassword) {}

    /**
     * @param  array{name: string, email: string, phone?: string|null, preferred_locale: string}  $data
     * @param  list<string>  $roles
     */
    public function __invoke(array $data, array $roles, User $createdBy): User
    {
        return DB::transaction(function () use ($data, $roles, $createdBy): User {
            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'preferred_locale' => $data['preferred_locale'],
                'password' => Str::password(40),
                'status' => AccountStatus::Active,
                'first_login' => true,
            ]);

            $user->syncRoles($roles);

            ($this->issueTemporaryPassword)($user, $createdBy);

            return $user;
        });
    }
}
