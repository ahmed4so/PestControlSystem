<?php

declare(strict_types=1);

namespace App\Domain\Workflow\Guards;

use App\Domain\Identity\Enums\AccountStatus;
use App\Domain\Identity\Enums\StaffRole;
use App\Domain\Workflow\Contracts\Guard;
use App\Domain\Workflow\Engine\GuardResult;
use App\Domain\Workflow\Engine\TransitionContext;
use App\Models\User;

/**
 * The staff user chosen in the payload (`assignee_id`) is active and holds
 * the role given as parameter (T5: `AssigneeHasRole:TECHNICAL_REVIEWER`).
 */
final class AssigneeHasRole implements Guard
{
    public const REASON = 'Choose an active staff member with the role :role.';

    public function check(TransitionContext $context, ?string $param): GuardResult
    {
        $role = StaffRole::fromCode((string) $param);
        $assignee = User::query()->find($context->payload('assignee_id'));

        $ok = $assignee instanceof User
            && $assignee->status === AccountStatus::Active
            && $assignee->hasRole($role->value);

        return $ok ? GuardResult::pass() : GuardResult::fail(__(self::REASON, ['role' => __($role->value)]));
    }

    public function needsPayload(): bool
    {
        return true;
    }
}
