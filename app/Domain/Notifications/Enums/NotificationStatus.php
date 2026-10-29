<?php

declare(strict_types=1);

namespace App\Domain\Notifications\Enums;

/**
 * Delivery status of a logged email (02-data-model §4.12).
 */
enum NotificationStatus: string
{
    case Queued = 'QUEUED';
    case Sent = 'SENT';
    case Failed = 'FAILED';
}
