<?php

declare(strict_types=1);

namespace App\Domain\Workflow\Checks;

use App\Domain\Workflow\Enums\WorkflowActionCode;
use App\Models\Application;
use App\Models\ReviewItem;
use App\Models\User;
use App\Models\WorkflowAction;

/**
 * Segregation of duties on one application (04 §10 rule 5, D-086):
 * - who recommended (RECOMMEND) cannot decide (APPROVE, DEFER, REJECT);
 * - who cleared the payment (CLEAR_PAYMENT) cannot decide;
 * - who recorded Registrar review items cannot issue the
 *   permission-to-pay letter (ISSUE_PERMISSION_TO_PAY).
 */
final class SegregationRules
{
    public const REASON_RECOMMENDED = 'You recommended this application, so you cannot decide on it.';

    public const REASON_CLEARED = 'You cleared the payment of this application, so you cannot decide on it.';

    public const REASON_REVIEWED = 'You reviewed this application as Registrar, so you cannot issue its permission-to-pay letter.';

    public static function breach(Application $application, User $user, WorkflowActionCode $action): ?string
    {
        if (in_array($action, WorkflowActionCode::dgDecisions(), true)) {
            if (self::performed($application, $user, WorkflowActionCode::Recommend)) {
                return self::REASON_RECOMMENDED;
            }

            if (self::performed($application, $user, WorkflowActionCode::ClearPayment)) {
                return self::REASON_CLEARED;
            }
        }

        if ($action === WorkflowActionCode::IssuePermissionToPay && self::recordedReviewItems($application, $user)) {
            return self::REASON_REVIEWED;
        }

        return null;
    }

    private static function performed(Application $application, User $user, WorkflowActionCode $action): bool
    {
        return WorkflowAction::query()
            ->where('application_id', $application->id)
            ->where('action', $action)
            ->where('actor_type', $user->getMorphClass())
            ->where('actor_id', $user->id)
            ->exists();
    }

    private static function recordedReviewItems(Application $application, User $user): bool
    {
        return ReviewItem::query()
            ->where('reviewed_by', $user->id)
            ->whereHas('version', fn ($query) => $query->where('application_id', $application->id))
            ->exists();
    }
}
