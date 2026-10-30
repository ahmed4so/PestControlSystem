<?php

declare(strict_types=1);

namespace App\Domain\Notifications\Mail;

use App\Domain\Notifications\Enums\NotificationCode;

/**
 * COMPANY_TEMP_PASSWORD (07 §3.1).
 */
final class CompanyTempPassword extends NotificationMail
{
    public function code(): NotificationCode
    {
        return NotificationCode::CompanyTempPassword;
    }
}
