<?php

declare(strict_types=1);

namespace App\Domain\Workflow\Checks;

use App\Domain\Workflow\Contracts\ActorCheck;
use App\Domain\Workflow\Engine\TransitionContext;
use App\Domain\Workflow\Enums\ActorRole;
use App\Models\CompanyUser;
use App\Models\User;

/**
 * Company actions belong to the application's own company account; staff
 * actions to staff users (04 §10 rule 1, D-048). SYSTEM actions are never
 * performed by a person.
 */
final class RealmCheck implements ActorCheck
{
    public const REASON = 'This action is not available to your account.';

    public function check(TransitionContext $context): ?string
    {
        $user = $context->actor->user;
        $actor = $context->transition->actor_role;

        $allowed = match (true) {
            $actor === ActorRole::Company => $user instanceof CompanyUser
                && $user->company_id !== null
                && $user->company_id === $context->application->company_id,
            $actor === ActorRole::System => false,
            default => $user instanceof User,
        };

        return $allowed && $user->isActive() ? null : self::REASON;
    }
}
