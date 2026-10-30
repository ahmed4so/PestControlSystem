<?php

declare(strict_types=1);

namespace App\Domain\Identity\Actions;

use App\Domain\Audit\Support\AuditLog;
use App\Domain\Identity\Support\AccountSessions;
use App\Domain\Notifications\Actions\SendNotification;
use App\Domain\Notifications\Enums\NotificationCode;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Staff 2FA reset by the System Administrator after face-to-face checks
 * (03 §5.2, D-087): clears 2FA, ends the user's sessions and makes them
 * enrol again at the next login.
 */
final class ResetTwoFactor
{
    public function __construct(private readonly SendNotification $notify) {}

    public function __invoke(User $account, User $resetBy): void
    {
        DB::transaction(function () use ($account, $resetBy): void {
            $account->forceFill([
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
            ])->saveQuietly();

            AccountSessions::end($account);

            AuditLog::record('two_factor_reset', 'Two-factor authentication reset', $account, $resetBy);

            ($this->notify)($account, NotificationCode::TwoFactorChanged, [
                'change' => 'reset',
                'url' => route($account->area()->route('login')),
            ]);
        });
    }
}
