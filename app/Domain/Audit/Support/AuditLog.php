<?php

declare(strict_types=1);

namespace App\Domain\Audit\Support;

use Illuminate\Database\Eloquent\Model;

/**
 * Writes an explicit security or account event to the audit log (03 §13),
 * for events that are not a plain model change: logins, lockouts,
 * temporary passwords, 2FA changes. Never pass secrets in $properties.
 */
final class AuditLog
{
    /**
     * @param  array<string, mixed>  $properties
     */
    public static function record(
        string $event,
        string $description,
        ?Model $subject = null,
        ?Model $causer = null,
        array $properties = [],
    ): void {
        $logger = activity('audit')->event($event)->withProperties($properties);

        if ($subject !== null) {
            $logger->performedOn($subject);
        }

        if ($causer !== null) {
            $logger->causedBy($causer);
        }

        $logger->log($description);
    }
}
