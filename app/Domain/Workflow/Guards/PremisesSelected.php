<?php

declare(strict_types=1);

namespace App\Domain\Workflow\Guards;

use App\Domain\Workflow\Contracts\Guard;
use App\Domain\Workflow\Engine\GuardResult;
use App\Domain\Workflow\Engine\TransitionContext;

/**
 * At least one premises is selected, and every selected premises belongs
 * to the application's company (05 §5.1, D-197).
 */
final class PremisesSelected implements Guard
{
    public const REASON_NONE = 'Select at least one premises.';

    public const REASON_OTHER = 'Select only your own premises.';

    public function check(TransitionContext $context, ?string $param): GuardResult
    {
        $premises = $context->application->premises()->withoutGlobalScope('company')->get();

        if ($premises->isEmpty()) {
            return GuardResult::fail(self::REASON_NONE);
        }

        $foreign = $premises->contains(fn ($item) => $item->company_id !== $context->application->company_id);

        return $foreign ? GuardResult::fail(self::REASON_OTHER) : GuardResult::pass();
    }

    public function needsPayload(): bool
    {
        return false;
    }
}
