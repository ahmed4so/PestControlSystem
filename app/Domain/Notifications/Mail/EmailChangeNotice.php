<?php

declare(strict_types=1);

namespace App\Domain\Notifications\Mail;

use App\Domain\Notifications\Enums\NotificationCode;

/**
 * EMAIL_CHANGE_NOTICE (07 §3.1).
 */
final class EmailChangeNotice extends NotificationMail
{
    public function code(): NotificationCode
    {
        return NotificationCode::EmailChangeNotice;
    }
}
