<?php

declare(strict_types=1);

namespace App\Domain\Notifications\Mail;

use App\Domain\Notifications\Enums\NotificationCode;

/**
 * PASSWORD_CHANGED (07 §3.1).
 */
final class PasswordChanged extends NotificationMail
{
    public function code(): NotificationCode
    {
        return NotificationCode::PasswordChanged;
    }
}
