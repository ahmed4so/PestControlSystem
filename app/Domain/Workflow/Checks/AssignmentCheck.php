<?php

declare(strict_types=1);

namespace App\Domain\Workflow\Checks;

use App\Domain\Workflow\Contracts\ActorCheck;
use App\Domain\Workflow\Engine\TransitionContext;
use App\Domain\Workflow\Enums\ActorRole;
use App\Models\Inspection;
use App\Models\StageAssignment;

/**
 * Technical Reviewer actions need an open assignment of this user to the
 * stage; Inspector actions need the inspection to be assigned to this user
 * (04 §10 rule 3).
 */
final class AssignmentCheck implements ActorCheck
{
    public const REASON = 'You are not assigned to this application.';

    public function check(TransitionContext $context): ?string
    {
        $actor = $context->transition->actor_role;

        if ($actor === ActorRole::Inspector) {
            // The inspection named in the payload, or (for availableActions,
            // which has no payload) any open inspection of this user.
            $inspections = Inspection::query()
                ->where('application_id', $context->application->id)
                ->where('inspector_id', $context->staffUser()?->id)
                ->open();
            $named = $context->payload('inspection_id');

            if (is_numeric($named)) {
                $inspections->whereKey((int) $named);
            }

            return $inspections->exists() ? null : self::REASON;
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
