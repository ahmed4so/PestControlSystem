<?php

declare(strict_types=1);

namespace App\Domain\Workflow\Guards;

use App\Domain\Applications\Enums\ApplicationType;
use App\Domain\Workflow\Contracts\Guard;
use App\Domain\Workflow\Engine\GuardResult;
use App\Domain\Workflow\Engine\TransitionContext;

/**
 * A guard whose data arrives in a later phase (D-188). It exists so that
 * definitions referencing it load and validate, and it refuses if it ever
 * runs, so nothing can pass an unbuilt rule. A guard that only applies to
 * some application types (04 §8) passes for the others.
 */
final class NotYetAvailableGuard implements Guard
{
    public const REASON = 'This step is not available yet.';

    /**
     * @param  list<ApplicationType>|null  $onlyFor
     */
    public function __construct(
        public readonly string $name,
        public readonly string $phase,
        private readonly ?array $onlyFor = null,
    ) {}

    public function check(TransitionContext $context, ?string $param): GuardResult
    {
        if ($this->onlyFor !== null && ! in_array($context->application->application_type, $this->onlyFor, true)) {
            return GuardResult::pass();
        }

        return GuardResult::fail(self::REASON);
    }

    public function needsPayload(): bool
    {
        return false;
    }
}
