<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;
use Spatie\Activitylog\Models\Activity;

/**
 * An append-only audit entry (D-055, D-091). The log is append-only by
 * application rule (D-160): this model refuses updates and deletes, and no
 * code path changes audit rows. There are no database triggers.
 */
class AuditActivity extends Activity
{
    protected static function booted(): void
    {
        static::creating(function (self $activity): void {
            $activity->actor_role ??= self::roleOf($activity->causer);

            if (app()->runningInConsole() && ! app()->runningUnitTests()) {
                return;
            }

            $activity->ip_address ??= request()->ip();
            $activity->user_agent ??= substr((string) request()->userAgent(), 0, 255) ?: null;
        });

        static::updating(fn () => throw new LogicException('Audit log entries cannot be updated.'));
        static::deleting(fn () => throw new LogicException('Audit log entries cannot be deleted.'));
    }

    /**
     * The role the actor held (03 §13): a company account, or the staff
     * member's roles.
     */
    private static function roleOf(?Model $causer): ?string
    {
        return match (true) {
            $causer instanceof CompanyUser => 'Company',
            $causer instanceof User => substr($causer->getRoleNames()->implode(', '), 0, 60) ?: null,
            default => null,
        };
    }
}
