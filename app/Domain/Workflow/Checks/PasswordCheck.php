<?php

declare(strict_types=1);

namespace App\Domain\Workflow\Checks;

use App\Domain\Workflow\Contracts\ActorCheck;
use App\Domain\Workflow\Engine\TransitionContext;

/**
 * Actions marked for password confirmation need it within the account's
 * window (04 §10 rule 6, D-085).
 */
final class PasswordCheck implements ActorCheck
{
    public const REASON = 'Confirm your password to continue.';

    public function check(TransitionContext $context): ?string
    {
        if (! $context->transition->requires_password) {
            return null;
        }

        return $context->actor->passwordRecentlyConfirmed() ? null : self::REASON;
    }
}
