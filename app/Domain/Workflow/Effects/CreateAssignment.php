<?php

declare(strict_types=1);

namespace App\Domain\Workflow\Effects;

use App\Domain\Workflow\Contracts\Effect;
use App\Domain\Workflow\Engine\TransitionContext;
use App\Models\StageAssignment;
use Illuminate\Validation\Rule;

/**
 * Assigns a Technical Reviewer to the current stage (04 §12, T5,
 * procedure 6.4). AssigneeHasRole checks the chosen user.
 */
final class CreateAssignment implements Effect
{
    public function apply(TransitionContext $context, ?string $param): void
    {
        $assignment = new StageAssignment([
            'stage' => $context->transition->from_stage,
            'assigned_user_id' => (int) $context->payload('assignee_id'),
            'assigned_by' => (int) $context->actor->user->getKey(),
            'assigned_at' => now(),
        ]);
        $assignment->application()->associate($context->application);
        $assignment->save();
    }

    public function payloadRules(?string $param): array
    {
        return ['assignee_id' => ['required', 'integer', Rule::exists('users', 'id')]];
    }
}
