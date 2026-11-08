<?php

declare(strict_types=1);

namespace App\Domain\Notifications\Mail;

use App\Domain\Notifications\Enums\NotificationCode;

/**
 * INSPECTION_SCHEDULED (07 §3.3).
 */
final class InspectionScheduled extends NotificationMail
{
    public function code(): NotificationCode
    {
        return NotificationCode::InspectionScheduled;
    }
}
