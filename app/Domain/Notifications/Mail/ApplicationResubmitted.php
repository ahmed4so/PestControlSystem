<?php

declare(strict_types=1);

namespace App\Domain\Notifications\Mail;

use App\Domain\Notifications\Enums\NotificationCode;

/**
 * APPLICATION_RESUBMITTED (07 §3.3).
 */
final class ApplicationResubmitted extends NotificationMail
{
    public function code(): NotificationCode
    {
        return NotificationCode::ApplicationResubmitted;
    }
}
