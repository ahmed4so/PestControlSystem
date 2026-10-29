<?php

declare(strict_types=1);

namespace App\Domain\Notifications\Mail;

use App\Domain\Notifications\Enums\NotificationCode;

/**
 * EMAIL_CHANGE_CONFIRM (07 §3.1).
 */
final class EmailChangeConfirm extends NotificationMail
{
    public function code(): NotificationCode
    {
        return NotificationCode::EmailChangeConfirm;
    }
}
