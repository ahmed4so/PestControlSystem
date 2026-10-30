<?php

declare(strict_types=1);

namespace App\Domain\Identity\Login;

use App\Domain\Audit\Support\AuditLog;
use App\Domain\Identity\Enums\AuthArea;
use App\Domain\Notifications\Actions\SendNotification;
use App\Domain\Notifications\Enums\NotificationCode;
use App\Models\CompanyUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;

/**
 * Fortify's credential check for both areas (03 §3, §7; D-081). It only
 * looks in the current area's table, so a staff account cannot log in on
 * the Company Portal and the reverse. Five consecutive failures lock the
 * account for 15 minutes. Until the correct password is given, every
 * failure shows the same message; only then does it say why the account
 * cannot log in (locked, temporary password expired, inactive).
 */
final class AuthenticateAccount
{
    private static ?string $dummyHash = null;

    public function __construct(private readonly SendNotification $notify) {}

    public function __invoke(Request $request): User|CompanyUser|null
    {
        $area = AuthArea::current($request) ?? AuthArea::Company;
        $email = Str::lower(trim((string) $request->input(Fortify::username())));
        $password = (string) $request->input('password');

        /** @var User|CompanyUser|null $account */
        $account = $area->model()::query()->where('email', $email)->first();

        if ($account === null) {
            Hash::check($password, self::dummyHash()); // similar timing for unknown emails
            AuditLog::record('login_failed', 'Login failed', properties: ['area' => $area->value, 'email' => $email]);

            return null;
        }

        if (! Hash::check($password, $account->getAuthPassword())) {
            $this->recordFailure($account);

            return null;
        }

        $this->ensureCanLogIn($account);

        return $account;
    }

    private function recordFailure(User|CompanyUser $account): void
    {
        DB::transaction(function () use ($account): void {
            if ($account->isLocked()) {
                AuditLog::record('login_failed', 'Login failed (account locked)', $account);

                return;
            }

            $attempts = $account->failed_login_attempts + 1;
            $lock = $attempts >= (int) config('identity.lockout.max_attempts');

            $account->forceFill([
                'failed_login_attempts' => $lock ? 0 : $attempts,
                'locked_until' => $lock ? now()->addMinutes((int) config('identity.lockout.minutes')) : null,
            ])->saveQuietly();

            AuditLog::record('login_failed', 'Login failed', $account);

            if ($lock) {
                AuditLog::record('account_locked', 'Account locked after failed logins', $account);

                ($this->notify)($account, NotificationCode::AccountLocked, [
                    'url' => route($account->area()->route('password.request')),
                ]);
            }
        });
    }

    private function ensureCanLogIn(User|CompanyUser $account): void
    {
        $message = match (true) {
            $account->isLocked() => __('Your account is locked after failed sign-in attempts. Try again in 15 minutes, or reset your password.'),
            ! $account->isActive() => __('Your account is not active. Please contact SARIS.'),
            $account->hasExpiredTemporaryPassword() => $account instanceof CompanyUser
                ? __('Your temporary password has expired. Request a new temporary password.')
                : __('Your temporary password has expired. Ask the System Administrator for a new one.'),
            default => null,
        };

        if ($message !== null) {
            AuditLog::record('login_refused', 'Login refused', $account, properties: ['reason' => $message]);

            throw ValidationException::withMessages([Fortify::username() => $message]);
        }
    }

    private static function dummyHash(): string
    {
        return self::$dummyHash ??= Hash::make(Str::random(40));
    }
}
