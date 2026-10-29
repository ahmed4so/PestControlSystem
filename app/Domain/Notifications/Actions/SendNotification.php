<?php

declare(strict_types=1);

namespace App\Domain\Notifications\Actions;

use App\Domain\Notifications\Enums\NotificationCode;
use App\Domain\Notifications\Enums\NotificationStatus;
use App\Models\CompanyUser;
use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

/**
 * The only way to send an email (07 §1, D-129): writes a QUEUED log row
 * and queues the code's Mailable, which is sent after the surrounding
 * transaction commits. If the transaction rolls back, neither survives.
 */
final class SendNotification
{
    /**
     * @param  array<string, string>  $data  template values; never stored
     * @param  string|null  $email  address to use instead of the account's (e.g. a new login email)
     */
    public function __invoke(
        User|CompanyUser $recipient,
        NotificationCode $code,
        array $data = [],
        ?string $email = null,
        ?Model $related = null,
        ?string $reference = null,
    ): NotificationLog {
        $email ??= $recipient->email;
        $locale = $recipient->preferredLocale();

        $log = new NotificationLog([
            'email' => $email,
            'template' => $code,
            'locale' => $locale,
            'status' => NotificationStatus::Queued,
        ]);
        $log->recipient()->associate($recipient);

        if ($related !== null) {
            $log->related()->associate($related);
        }

        $log->save();

        $mailable = $code->mailable();

        Mail::to($email)->queue(new $mailable(
            $log->id,
            $locale,
            ['name' => $recipient->displayName(), ...$data],
            $reference,
        ));

        return $log;
    }
}
