<?php

declare(strict_types=1);

namespace App\Domain\Workflow\Guards;

use App\Domain\Workflow\Contracts\Guard;
use App\Domain\Workflow\Engine\GuardResult;
use App\Domain\Workflow\Engine\TransitionContext;
use App\Models\StageAssignment;

/**
 * No unfinished Technical Reviewer assignment (04 §11, procedure 6.4).
 */
final class NoOpenAssignment implements Guard
{
    public const REASON = 'Wait until the assigned Technical Reviewer has finished.';

    public function check(TransitionContext $context, ?string $param): GuardResult
    {
        $open = StageAssignment::query()
            ->where('application_id', $context->application->id)
            ->open()
            ->exists();

        return $open ? GuardResult::fail(self::REASON) : GuardResult::pass();
    }

    public function needsPayload(): bool
    {
        return false;
    }
}
