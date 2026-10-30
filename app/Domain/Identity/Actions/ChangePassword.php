<?php

declare(strict_types=1);

namespace App\Domain\Identity\Actions;

use App\Domain\Audit\Support\AuditLog;
use App\Domain\Identity\Support\AccountSessions;
use App\Domain\Notifications\Actions\SendNotification;
use App\Domain\Notifications\Enums\NotificationCode;
use App\Models\CompanyUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Sets a new password for either realm (03 §6, D-082): keeps the old hash
 * for the reuse check, clears first-login, temporary-password and lockout
 * state, ends the account's other sessions and sends PASSWORD_CHANGED.
 * Callers validate the new password first (policy and NotRecentPassword).
 */
final class ChangePassword
{
    public function __construct(private readonly SendNotification $notify) {}

    public function __invoke(
        User|CompanyUser $account,
        #[\SensitiveParameter] string $newPassword,
        ?string $keepSessionId = null,
        string $event = 'password_changed',
    ): void {
        DB::transaction(function () use ($account, $newPassword, $keepSessionId, $event): void {
            $account->passwordHistories()->create(['password_hash' => $account->getAuthPassword()]);

            $account->forceFill([
                'password' => $newPassword,
                'password_changed_at' => now(),
                'first_login' => false,
                'temp_password_expires_at' => null,
                'failed_login_attempts' => 0,
                'locked_until' => null,
                'email_verified_at' => $account->email_verified_at ?? now(),
            ])->saveQuietly();

            AccountSessions::end($account, $keepSessionId);

            AuditLog::record($event, $event === 'password_reset' ? 'Password reset' : 'Password changed', $account, $account);

            ($this->notify)($account, NotificationCode::PasswordChanged, [
                'url' => route($account->area()->route('login')),
            ]);
        });
    }
}
