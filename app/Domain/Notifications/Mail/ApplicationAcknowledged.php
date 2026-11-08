<?php

declare(strict_types=1);

namespace App\Domain\Notifications\Mail;

use App\Domain\Notifications\Enums\NotificationCode;

/**
 * APPLICATION_ACKNOWLEDGED (07 §3.3).
 */
final class ApplicationAcknowledged extends NotificationMail
{
    public function code(): NotificationCode
    {
        return NotificationCode::ApplicationAcknowledged;
    }
}
