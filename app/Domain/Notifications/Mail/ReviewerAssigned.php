<?php

declare(strict_types=1);

namespace App\Domain\Notifications\Mail;

use App\Domain\Notifications\Enums\NotificationCode;

/**
 * REVIEWER_ASSIGNED (07 §3.3).
 */
final class ReviewerAssigned extends NotificationMail
{
    public function code(): NotificationCode
    {
        return NotificationCode::ReviewerAssigned;
    }
}
