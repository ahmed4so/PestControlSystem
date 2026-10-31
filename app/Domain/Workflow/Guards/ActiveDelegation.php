<?php

declare(strict_types=1);

namespace App\Domain\Workflow\Guards;

use App\Domain\Identity\Enums\StaffRole;
use App\Domain\Workflow\Checks\DelegationCheck;
use App\Domain\Workflow\Contracts\Guard;
use App\Domain\Workflow\Engine\GuardResult;
use App\Domain\Workflow\Engine\TransitionContext;

/**
 * A delegate is within the delegation dates (04 §11, D-027). Passes for
 * the Director General.
 */
final class ActiveDelegation implements Guard
{
    public function check(TransitionContext $context, ?string $param): GuardResult
    {
        $user = $context->staffUser();

        if ($user === null || $user->hasRole(StaffRole::DirectorGeneral->value)) {
            return GuardResult::pass();
        }

        return DelegationCheck::activeDelegation($user) === null
            ? GuardResult::fail(DelegationCheck::REASON)
            : GuardResult::pass();
    }

    public function needsPayload(): bool
    {
        return false;
    }
}
