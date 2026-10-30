<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Identity\Enums\AccountStatus;
use App\Domain\Notifications\Actions\SendNotification;
use App\Domain\Notifications\Enums\NotificationCode;
use App\Models\CompanyUser;
use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Console\Command;

/**
 * Sends PASSWORD_EXPIRY_REMINDER 14 days before the 90-day expiry, once per
 * password (03 §6, D-082). Runs daily.
 */
class PasswordExpiryRemindersCommand extends Command
{
    protected $signature = 'identity:password-expiry-reminders';

    protected $description = 'Email accounts whose password expires within 14 days';

    public function handle(SendNotification $notify): int
    {
        $sent = 0;
        $days = (int) config('identity.password.expires_after_days');
        $before = (int) config('identity.password.reminder_days_before');

        foreach ([User::class, CompanyUser::class] as $model) {
            $model::query()
                ->where('status', AccountStatus::Active)
                ->where('first_login', false)
                ->whereNotNull('password_changed_at')
                ->where('password_changed_at', '<=', now()->subDays($days - $before))
                ->where('password_changed_at', '>', now()->subDays($days))
                ->chunkById(200, function ($accounts) use ($notify, &$sent): void {
                    foreach ($accounts as $account) {
                        /** @var User|CompanyUser $account */
                        $alreadySent = NotificationLog::query()
                            ->whereMorphedTo('recipient', $account)
                            ->where('template', NotificationCode::PasswordExpiryReminder)
                            ->where('created_at', '>=', $account->password_changed_at)
                            ->exists();

                        if ($alreadySent) {
                            continue;
                        }

                        $notify($account, NotificationCode::PasswordExpiryReminder, [
                            'date' => (string) $account->passwordExpiresAt()?->timezone('Africa/Mogadishu')->format('d/m/Y'),
                            'url' => route($account->area()->route('user-password.edit')),
                        ]);
                        $sent++;
                    }
                });
        }

        $this->info("Password expiry reminders sent: {$sent}");

        return self::SUCCESS;
    }
}
