<?php

declare(strict_types=1);

namespace App\Domain\Workflow\Contracts;

use App\Domain\Workflow\Engine\TransitionContext;

/**
 * One of the actor checks of 04 §10, run in a fixed order before any
 * guard: realm, role, assignment, delegation, segregation, password.
 */
interface ActorCheck
{
    /**
     * Null when the check passes; otherwise the reason (an English key).
     */
    public function check(TransitionContext $context): ?string;
}
