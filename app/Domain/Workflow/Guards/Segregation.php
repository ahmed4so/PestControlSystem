<?php

declare(strict_types=1);

namespace App\Domain\Workflow\Guards;

use App\Domain\Workflow\Checks\SegregationRules;
use App\Domain\Workflow\Contracts\Guard;
use App\Domain\Workflow\Engine\GuardResult;
use App\Domain\Workflow\Engine\TransitionContext;

/**
 * Segregation of duties as a named guard (04 §11, D-086). The engine also
 * applies the same rules to every staff action (SegregationCheck).
 */
final class Segregation implements Guard
{
    public function check(TransitionContext $context, ?string $param): GuardResult
    {
        $user = $context->staffUser();
        $reason = $user === null ? null : SegregationRules::breach($context->application, $user, $context->transition->action);

        return $reason === null ? GuardResult::pass() : GuardResult::fail($reason);
    }

    public function needsPayload(): bool
    {
        return false;
    }
}
