<?php

declare(strict_types=1);

namespace App\Domain\Workflow\Checks;

use App\Domain\Identity\Enums\StaffRole;
use App\Domain\Workflow\Contracts\ActorCheck;
use App\Domain\Workflow\Engine\TransitionContext;
use App\Domain\Workflow\Enums\ActorRole;
use App\Models\Delegation;
use App\Models\User;

/**
 * A user without the Director General role may perform DG actions only
 * within an active, unrevoked delegation; the action then records who
 * they act for (04 §10 rule 4, D-027).
 */
final class DelegationCheck implements ActorCheck
{
    public const REASON = 'You can act for the Director General only during an active delegation.';

    public function check(TransitionContext $context): ?string
    {
        $user = $context->staffUser();

        if ($context->transition->actor_role !== ActorRole::DirectorGeneral || $user === null) {
            return null;
        }

        if ($user->hasRole(StaffRole::DirectorGeneral->value)) {
            return null;
        }

        $delegation = self::activeDelegation($user);

        if ($delegation === null) {
            return self::REASON;
        }

        $context->onBehalfOf = $delegation->delegator;

        return null;
    }

    public static function activeDelegation(User $user): ?Delegation
    {
        return Delegation::query()->activeFor($user)->with('delegator')->latest('starts_on')->first();
    }
}
