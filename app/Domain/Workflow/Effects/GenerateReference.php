<?php

declare(strict_types=1);

namespace App\Domain\Workflow\Effects;

use App\Domain\ReferenceData\Services\NumberSequencer;
use App\Domain\Workflow\Contracts\Effect;
use App\Domain\Workflow\Engine\TransitionContext;

/**
 * `CL-`, `OL-`, `IP-YYYY-NNNNN` on first submit (04 §12, D-075).
 */
final class GenerateReference implements Effect
{
    public function __construct(private readonly NumberSequencer $numbers) {}

    public function apply(TransitionContext $context, ?string $param): void
    {
        $application = $context->application;

        if ($application->reference === null) {
            $application->reference = $this->numbers->next($application->service->referencePrefix());
        }

        $application->first_submitted_at ??= now();
    }

    public function payloadRules(?string $param): array
    {
        return [];
    }
}
