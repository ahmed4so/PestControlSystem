<?php

declare(strict_types=1);

namespace App\Domain\Notifications\Mail;

use App\Domain\Notifications\Enums\NotificationCode;

/**
 * COMPANY_ACCOUNT_EXISTS (07 §3.1).
 */
final class CompanyAccountExists extends NotificationMail
{
    public function code(): NotificationCode
    {
        return NotificationCode::CompanyAccountExists;
    }
}
