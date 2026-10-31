<?php

declare(strict_types=1);

namespace App\Domain\Workflow\Effects;

use App\Domain\Workflow\Contracts\Effect;
use App\Domain\Workflow\Engine\TransitionContext;
use App\Models\StageAssignment;

/**
 * Ends the acting Technical Reviewer's open assignment to the stage
 * (04 §12, T6).
 */
final class EndAssignment implements Effect
{
    public function apply(TransitionContext $context, ?string $param): void
    {
        StageAssignment::query()
            ->where('application_id', $context->application->id)
            ->where('stage', $context->transition->from_stage)
            ->where('assigned_user_id', $context->actor->user->getKey())
            ->open()
            ->get()
            ->each(fn (StageAssignment $assignment) => $assignment->update(['ended_at' => now()]));
    }

    public function payloadRules(?string $param): array
    {
        return [];
    }
}
