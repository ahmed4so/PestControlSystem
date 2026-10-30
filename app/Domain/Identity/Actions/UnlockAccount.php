<?php

declare(strict_types=1);

namespace App\Domain\Identity\Actions;

use App\Domain\Audit\Support\AuditLog;
use App\Models\User;

/**
 * Early unlock of a locked staff account by the System Administrator
 * (03 §7, D-081). Company accounts unlock by resetting their password.
 */
final class UnlockAccount
{
    public function __invoke(User $account, User $unlockedBy): void
    {
        $account->forceFill([
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ])->saveQuietly();

        AuditLog::record('account_unlocked', 'Account unlocked', $account, $unlockedBy);
    }
}
