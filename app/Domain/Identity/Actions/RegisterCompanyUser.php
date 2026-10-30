<?php

declare(strict_types=1);

namespace App\Domain\Identity\Actions;

use App\Domain\Audit\Support\AuditLog;
use App\Domain\Identity\Enums\AccountStatus;
use App\Domain\Notifications\Actions\SendNotification;
use App\Domain\Notifications\Enums\NotificationCode;
use App\Models\CompanyUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Company sign-up (03 §4, D-063). An email that already belongs to an
 * activated account only gets "you already have an account"; a new or
 * never-activated email gets a fresh temporary password. The caller shows
 * the same message in every case.
 */
final class RegisterCompanyUser
{
    public function __construct(
        private readonly IssueTemporaryPassword $issueTemporaryPassword,
        private readonly SendNotification $notify,
    ) {}

    /**
     * @param  array{full_name: string, email: string, phone: string}  $data
     */
    public function __invoke(array $data, string $locale): void
    {
        DB::transaction(function () use ($data, $locale): void {
            $account = CompanyUser::withTrashed()->where('email', $data['email'])->lockForUpdate()->first();

            if ($account !== null && ($account->trashed() || $account->last_login_at !== null)) {
                if (! $account->trashed()) {
                    ($this->notify)($account, NotificationCode::CompanyAccountExists, [
                        'url' => route('portal.password.request'),
                    ]);
                }

                AuditLog::record('signup_existing_account', 'Sign-up with an existing account email', $account);

                return;
            }

            $account ??= new CompanyUser;

            $account->forceFill([
                'full_name' => $data['full_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => Str::password(40),
                'status' => AccountStatus::Active,
                'first_login' => true,
                'preferred_locale' => $locale,
                'terms_accepted_at' => now(),
            ])->save();

            ($this->issueTemporaryPassword)($account);
        });
    }
}
