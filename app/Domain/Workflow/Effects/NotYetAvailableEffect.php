<?php

declare(strict_types=1);

namespace App\Domain\Workflow\Effects;

use App\Domain\Applications\Enums\ApplicationType;
use App\Domain\Workflow\Contracts\Effect;
use App\Domain\Workflow\Engine\TransitionContext;
use LogicException;

/**
 * An effect whose data arrives in a later phase (D-188). It exists so that
 * definitions referencing it load and validate, and it throws if it ever
 * runs, which rolls the whole transition back. An effect that only applies
 * to some application types (04 §8) does nothing for the others.
 */
final class NotYetAvailableEffect implements Effect
{
    /**
     * @param  list<ApplicationType>|null  $onlyFor
     */
    public function __construct(
        public readonly string $name,
        public readonly string $phase,
        private readonly ?array $onlyFor = null,
    ) {}

    public function apply(TransitionContext $context, ?string $param): void
    {
        if ($this->onlyFor !== null && ! in_array($context->application->application_type, $this->onlyFor, true)) {
            return;
        }

        throw new LogicException("The workflow effect [{$this->name}] is built in {$this->phase} (D-188).");
    }

    public function payloadRules(?string $param): array
    {
        return [];
    }
}
