<?php

declare(strict_types=1);

namespace App\Models;

use LogicException;
use Spatie\Activitylog\Models\Activity;

/**
 * An append-only audit entry (D-055, D-091). The database triggers reject
 * changes as well; this model refuses them before they reach the database.
 */
class AuditActivity extends Activity
{
    protected static function booted(): void
    {
        static::creating(function (self $activity): void {
            if (app()->runningInConsole() && ! app()->runningUnitTests()) {
                return;
            }

            $activity->ip_address ??= request()->ip();
            $activity->user_agent ??= substr((string) request()->userAgent(), 0, 255) ?: null;
        });

        static::updating(fn () => throw new LogicException('Audit log entries cannot be updated.'));
        static::deleting(fn () => throw new LogicException('Audit log entries cannot be deleted.'));
    }
}
