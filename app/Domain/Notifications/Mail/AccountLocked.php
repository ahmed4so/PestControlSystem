<?php

declare(strict_types=1);

namespace App\Domain\Notifications\Mail;

use App\Domain\Notifications\Enums\NotificationCode;

/**
 * ACCOUNT_LOCKED (07 §3.1).
 */
final class AccountLocked extends NotificationMail
{
    public function code(): NotificationCode
    {
        return NotificationCode::AccountLocked;
    }
}
