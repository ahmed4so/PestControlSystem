<?php

declare(strict_types=1);

namespace App\Domain\Notifications\Mail;

use App\Domain\Notifications\Enums\NotificationCode;

/**
 * TECHNICAL_REVIEW_DONE (07 §3.3).
 */
final class TechnicalReviewDone extends NotificationMail
{
    public function code(): NotificationCode
    {
        return NotificationCode::TechnicalReviewDone;
    }
}
