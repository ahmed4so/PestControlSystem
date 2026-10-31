<?php

declare(strict_types=1);

namespace App\Domain\Workflow\Contracts;

use App\Domain\Workflow\Engine\GuardResult;
use App\Domain\Workflow\Engine\TransitionContext;

/**
 * A named business rule that must pass before a transition runs (04 §11).
 * Referenced in definitions as `Name` or `Name:param`.
 */
interface Guard
{
    public function check(TransitionContext $context, ?string $param): GuardResult;

    /**
     * True when the guard checks input that only exists when the action is
     * performed; availableActions() leaves such guards out (D-191).
     */
    public function needsPayload(): bool;
}
