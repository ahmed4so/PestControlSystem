<?php

declare(strict_types=1);

namespace App\Domain\Identity\Listeners;

use App\Domain\Audit\Support\AuditLog;
use App\Http\Middleware\EnforceSessionTimeouts;
use App\Models\CompanyUser;
use App\Models\User;
use Illuminate\Auth\Events\Login;

/**
 * After a successful login (with 2FA if enabled): resets the lockout
 * counter (D-081), records the login time, marks the email as verified on
 * the first login with an emailed temporary password (D-063, D-165) and
 * starts the staff 12-hour limit (D-084).
 */
final class RecordSuccessfulLogin
{
    public const SESSION_STARTED_AT = 'auth.logged_in_at';

    public function handle(Login $event): void
    {
        $account = $event->user;

        if (! $account instanceof User && ! $account instanceof CompanyUser) {
            return;
        }

        $account->forceFill([
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
            'email_verified_at' => $account->email_verified_at ?? now(),
        ])->saveQuietly();

        if (request()->hasSession()) {
            request()->session()->put([
                self::SESSION_STARTED_AT => now()->getTimestamp(),
                EnforceSessionTimeouts::LAST_ACTIVITY => now()->getTimestamp(),
            ]);
        }

        AuditLog::record('login', 'Login succeeded', $account, $account);
    }
}
