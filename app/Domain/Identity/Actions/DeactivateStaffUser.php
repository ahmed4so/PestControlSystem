<?php

declare(strict_types=1);

namespace App\Domain\Identity\Actions;

use App\Domain\Identity\Enums\AccountStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Deactivates a staff user and ends all of their sessions immediately
 * (D-053, 03-auth-security §5.2). EnsureUserIsActive also logs the user out
 * on their next request.
 */
class DeactivateStaffUser
{
    public function __invoke(User $user): void
    {
        DB::transaction(function () use ($user): void {
            $user->update(['status' => AccountStatus::Inactive]);

            DB::table(config('session.table') ?? 'sessions')->where('user_id', $user->getKey())->delete();
        });
    }
}
