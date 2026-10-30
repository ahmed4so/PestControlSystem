<?php

declare(strict_types=1);

namespace App\Domain\Notifications\Mail;

use App\Domain\Notifications\Enums\NotificationCode;

/**
 * TWO_FACTOR_CHANGED (07 §3.1).
 */
final class TwoFactorChanged extends NotificationMail
{
    public function code(): NotificationCode
    {
        return NotificationCode::TwoFactorChanged;
    }
}
