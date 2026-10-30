<?php

declare(strict_types=1);

namespace App\Domain\Identity\Actions;

use App\Domain\Audit\Support\AuditLog;
use App\Domain\Identity\Support\TemporaryPassword;
use App\Domain\Notifications\Actions\SendNotification;
use App\Domain\Notifications\Enums\NotificationCode;
use App\Models\CompanyUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Gives an account a new temporary password and emails it (03 §4, §5.2):
 * valid 1 hour for companies (D-063) and 24 hours for staff (D-083). The
 * previous password stops working at once; only the hash is stored.
 */
final class IssueTemporaryPassword
{
    public function __construct(private readonly SendNotification $notify) {}

    public function __invoke(User|CompanyUser $account, ?User $issuedBy = null): void
    {
        DB::transaction(function () use ($account, $issuedBy): void {
            $password = TemporaryPassword::generate();
            $isCompany = $account instanceof CompanyUser;

            $account->forceFill([
                'password' => $password,
                'temp_password_expires_at' => $isCompany
                    ? now()->addMinutes((int) config('identity.temporary_password.company_minutes'))
                    : now()->addHours((int) config('identity.temporary_password.staff_hours')),
                'first_login' => true,
                'failed_login_attempts' => 0,
                'locked_until' => null,
            ])->saveQuietly();

            AuditLog::record('temporary_password_sent', 'Temporary password sent', $account, $issuedBy ?? $account);

            ($this->notify)(
                $account,
                $isCompany ? NotificationCode::CompanyTempPassword : NotificationCode::StaffTempPassword,
                [
                    'password' => $password,
                    'url' => route($account->area()->route('login')),
                ],
            );
        });
    }
}
