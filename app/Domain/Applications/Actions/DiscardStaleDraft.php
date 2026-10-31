<?php

declare(strict_types=1);

namespace App\Domain\Applications\Actions;

use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Audit\Support\AuditLog;
use App\Domain\Workflow\Exceptions\TransitionRefused;
use App\Models\Application;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * The System Administrator discards a draft that was never submitted and
 * has not been touched for the configured period (D-193, D-194). The draft
 * is soft deleted and its open key cleared, so the company can start a new
 * application of the same service and category. Companies never delete
 * anything (D-187).
 */
final class DiscardStaleDraft
{
    public const PERMISSION = 'discard stale drafts';

    public const REASON_PERMISSION = 'You cannot discard drafts.';

    public const REASON_SUBMITTED = 'Only a draft that was never submitted can be discarded.';

    public const REASON_RECENT = 'This draft was changed less than :days days ago.';

    public function handle(Application $application, User $administrator): void
    {
        if (! $administrator->isActive() || ! $administrator->can(self::PERMISSION)) {
            throw TransitionRefused::because(self::REASON_PERMISSION);
        }

        DB::transaction(function () use ($application, $administrator): void {
            $locked = Application::query()->withoutGlobalScope('company')->whereKey($application->id)->lockForUpdate()->firstOrFail();

            if (! self::neverSubmitted($locked)) {
                throw TransitionRefused::because(self::REASON_SUBMITTED);
            }

            if (! self::isStale($locked)) {
                throw TransitionRefused::because(__(self::REASON_RECENT, ['days' => self::days()]));
            }

            $locked->open_key = null;
            $locked->save();
            $locked->delete();

            AuditLog::record('draft_discarded', 'Stale draft discarded', $locked, $administrator, [
                'company_id' => $locked->company_id,
                'service' => $locked->service->value,
            ]);
        });
    }

    public static function neverSubmitted(Application $application): bool
    {
        return $application->status === ApplicationStatus::Draft
            && $application->first_submitted_at === null
            && $application->reference === null
            && $application->current_version_no === 0;
    }

    /**
     * Untouched for the whole period: saving the draft would have moved
     * `updated_at`.
     */
    public static function isStale(Application $application): bool
    {
        return $application->updated_at !== null
            && $application->updated_at->lte(now()->subDays(self::days()));
    }

    public static function days(): int
    {
        return (int) config('applications.stale_draft_days', 30);
    }
}
