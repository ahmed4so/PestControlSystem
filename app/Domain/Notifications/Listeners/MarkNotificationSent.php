<?php

declare(strict_types=1);

namespace App\Domain\Notifications\Listeners;

use App\Domain\Notifications\Enums\NotificationStatus;
use App\Domain\Notifications\Mail\NotificationMail;
use App\Models\NotificationLog;
use Illuminate\Mail\Events\MessageSent;

/**
 * Marks the log row SENT once the mailer accepts the message, with the
 * Resend message ID when there is one (02-data-model §4.12).
 */
final class MarkNotificationSent
{
    public function handle(MessageSent $event): void
    {
        $headers = $event->message->getHeaders();
        $logId = $headers->get(NotificationMail::LOG_HEADER)?->getBodyAsString();

        if ($logId === null || ! ctype_digit($logId)) {
            return;
        }

        $providerId = $headers->get('X-Resend-Email-ID')?->getBodyAsString() ?? $event->sent->getMessageId();

        NotificationLog::query()->whereKey((int) $logId)->update([
            'status' => NotificationStatus::Sent,
            'sent_at' => now(),
            'provider_message_id' => substr($providerId, 0, 100),
            'error' => null,
        ]);
    }
}
