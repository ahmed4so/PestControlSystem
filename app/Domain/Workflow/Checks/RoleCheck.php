<?php

declare(strict_types=1);

namespace App\Domain\Workflow\Checks;

use App\Domain\Workflow\Contracts\ActorCheck;
use App\Domain\Workflow\Engine\TransitionContext;
use App\Domain\Workflow\Enums\ActorRole;

/**
 * The staff user holds the transition's role (04 §10 rule 2). A user
 * without the Director General role may still act for the DG through an
 * active delegation; DelegationCheck decides that.
 */
final class RoleCheck implements ActorCheck
{
    public const REASON = 'You do not hold the role needed for this action.';

    public function check(TransitionContext $context): ?string
    {
        $user = $context->staffUser();
        $role = $context->transition->actor_role->staffRole();

        if ($user === null || $role === null) {
            return null;
        }

        if ($user->hasRole($role->value) || $context->transition->actor_role === ActorRole::DirectorGeneral) {
            return null;
        }

        return self::REASON;
    }
}
