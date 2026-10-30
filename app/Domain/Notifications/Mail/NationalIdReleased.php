<?php

declare(strict_types=1);

namespace App\Domain\Notifications\Mail;

use App\Domain\Notifications\Enums\NotificationCode;

/**
 * NATIONAL_ID_RELEASED (07 §3.2).
 */
final class NationalIdReleased extends NotificationMail
{
    public function code(): NotificationCode
    {
        return NotificationCode::NationalIdReleased;
    }
}
