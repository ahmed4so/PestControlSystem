<?php

declare(strict_types=1);

namespace App\Domain\Notifications\Mail;

use App\Domain\Notifications\Enums\NotificationCode;

/**
 * PASSWORD_EXPIRY_REMINDER (07 §3.1).
 */
final class PasswordExpiryReminder extends NotificationMail
{
    public function code(): NotificationCode
    {
        return NotificationCode::PasswordExpiryReminder;
    }
}
