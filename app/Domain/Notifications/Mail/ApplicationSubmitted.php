<?php

declare(strict_types=1);

namespace App\Domain\Notifications\Mail;

use App\Domain\Notifications\Enums\NotificationCode;

/**
 * APPLICATION_SUBMITTED (07 §3.3).
 */
final class ApplicationSubmitted extends NotificationMail
{
    public function code(): NotificationCode
    {
        return NotificationCode::ApplicationSubmitted;
    }
}
