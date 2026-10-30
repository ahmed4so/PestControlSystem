<?php

declare(strict_types=1);

namespace App\Domain\Identity\Actions;

use App\Domain\Audit\Support\AuditLog;
use App\Domain\Notifications\Actions\SendNotification;
use App\Domain\Notifications\Enums\NotificationCode;
use App\Models\CompanyUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Starts a company login-email change (03 §5.1, D-093): a link valid 60
 * minutes goes to the new address and a notice to the old one. The email
 * changes only when the link is used. Only the token's hash is stored.
 */
final class RequestLoginEmailChange
{
    public function __construct(private readonly SendNotification $notify) {}

    public function __invoke(CompanyUser $account, string $newEmail): void
    {
        DB::transaction(function () use ($account, $newEmail): void {
            $token = Str::random(64);

            $account->forceFill([
                'pending_email' => $newEmail,
                'pending_email_token_hash' => hash('sha256', $token),
                'pending_email_expires_at' => now()->addMinutes((int) config('identity.email_change_minutes')),
            ])->saveQuietly();

            AuditLog::record('email_change_requested', 'Login email change requested', $account, $account, [
                'new_email' => $newEmail,
            ]);

            ($this->notify)($account, NotificationCode::EmailChangeConfirm, [
                'url' => route('portal.email.confirm', ['token' => $token]),
            ], email: $newEmail);

            ($this->notify)($account, NotificationCode::EmailChangeNotice, [
                'new_email' => $newEmail,
            ]);
        });
    }
}
