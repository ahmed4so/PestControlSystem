<?php

declare(strict_types=1);

namespace App\Domain\Notifications\Mail;

use App\Domain\Notifications\Enums\NotificationCode;

/**
 * PASSWORD_RESET_LINK (07 §3.1).
 */
final class PasswordResetLink extends NotificationMail
{
    public function code(): NotificationCode
    {
        return NotificationCode::PasswordResetLink;
    }
}
