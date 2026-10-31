<?php

declare(strict_types=1);

namespace App\Domain\Workflow\Checks;

use App\Domain\Workflow\Contracts\ActorCheck;
use App\Domain\Workflow\Engine\TransitionContext;

/**
 * Segregation of duties, always applied to staff actions (04 §10 rule 5,
 * D-086).
 */
final class SegregationCheck implements ActorCheck
{
    public function check(TransitionContext $context): ?string
    {
        $user = $context->staffUser();

        return $user === null
            ? null
            : SegregationRules::breach($context->application, $user, $context->transition->action);
    }
}
