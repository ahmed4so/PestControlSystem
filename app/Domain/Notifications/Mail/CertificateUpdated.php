<?php

declare(strict_types=1);

namespace App\Domain\Notifications\Mail;

use App\Domain\Notifications\Enums\NotificationCode;

/**
 * CERTIFICATE_UPDATED (07 §3.2).
 */
final class CertificateUpdated extends NotificationMail
{
    public function code(): NotificationCode
    {
        return NotificationCode::CertificateUpdated;
    }
}
