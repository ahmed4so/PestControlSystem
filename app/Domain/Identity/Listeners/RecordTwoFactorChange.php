<?php

declare(strict_types=1);

namespace App\Domain\Identity\Listeners;

use App\Domain\Audit\Support\AuditLog;
use App\Domain\Notifications\Actions\SendNotification;
use App\Domain\Notifications\Enums\NotificationCode;
use App\Models\CompanyUser;
use App\Models\User;
use Laravel\Fortify\Events\RecoveryCodeReplaced;
use Laravel\Fortify\Events\TwoFactorAuthenticationConfirmed;
use Laravel\Fortify\Events\TwoFactorAuthenticationDisabled;

/**
 * Audits 2FA changes and sends TWO_FACTOR_CHANGED (03 §13, 07 §3.1).
 */
final class RecordTwoFactorChange
{
    public function __construct(private readonly SendNotification $notify) {}

    public function enabled(TwoFactorAuthenticationConfirmed $event): void
    {
        $this->record($event->user, 'enabled');
    }

    public function disabled(TwoFactorAuthenticationDisabled $event): void
    {
        $this->record($event->user, 'disabled');
    }

    public function recoveryCodeUsed(RecoveryCodeReplaced $event): void
    {
        if ($event->user instanceof User || $event->user instanceof CompanyUser) {
            AuditLog::record('two_factor_recovery_code_used', 'Two-factor recovery code used', $event->user, $event->user);
        }
    }

    private function record(mixed $account, string $change): void
    {
        if (! $account instanceof User && ! $account instanceof CompanyUser) {
            return;
        }

        AuditLog::record('two_factor_'.$change, 'Two-factor authentication '.$change, $account, $account);

        ($this->notify)($account, NotificationCode::TwoFactorChanged, [
            'change' => $change,
            'url' => route($account->area()->route('two-factor.show')),
        ]);
    }
}
