<?php

declare(strict_types=1);

namespace App\Domain\Notifications\Mail;

use App\Domain\Notifications\Enums\NotificationCode;

/**
 * INSPECTION_RESCHEDULED (07 §3.3).
 */
final class InspectionRescheduled extends NotificationMail
{
    public function code(): NotificationCode
    {
        return NotificationCode::InspectionRescheduled;
    }
}
