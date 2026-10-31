<?php

declare(strict_types=1);

namespace App\Domain\Workflow\Checks;

use App\Domain\Workflow\Contracts\ActorCheck;
use App\Domain\Workflow\Engine\TransitionContext;
use App\Domain\Workflow\Enums\ActorRole;
use App\Models\StageAssignment;

/**
 * Technical Reviewer actions need an open assignment of this user to the
 * stage; Inspector actions need the inspection to be assigned to this user
 * (04 §10 rule 3). Inspections arrive in Phase 4A; until then Inspector
 * actions are refused (D-188).
 */
final class AssignmentCheck implements ActorCheck
{
    public const REASON = 'You are not assigned to this application.';

    public function check(TransitionContext $context): ?string
    {
        $actor = $context->transition->actor_role;

        if ($actor === ActorRole::Inspector) {
            return self::REASON;
        }

        if ($actor !== ActorRole::TechnicalReviewer) {
            return null;
        }

        $assigned = StageAssignment::query()
            ->where('application_id', $context->application->id)
            ->where('stage', $context->transition->from_stage)
            ->where('assigned_user_id', $context->staffUser()?->id)
            ->open()
            ->exists();

        return $assigned ? null : self::REASON;
    }
}
