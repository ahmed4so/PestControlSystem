<?php

declare(strict_types=1);

namespace App\Domain\Workflow\Effects;

use App\Domain\Workflow\Contracts\Effect;
use App\Domain\Workflow\Engine\TransitionContext;

/**
 * Small effects that only set fields of the application (04 §12):
 * SetOpenKey / ClearOpenKey (D-044, D-072, D-193), SetResumeStage:STAGE /
 * ClearResumeStage (D-043) and SetDecidedAt.
 */
final class ApplicationFieldEffect implements Effect
{
    public function __construct(private readonly string $field) {}

    public function apply(TransitionContext $context, ?string $param): void
    {
        $application = $context->application;

        match ($this->field) {
            'set_open_key' => $application->open_key = $application->service->openKey($application->license_category),
            'clear_open_key' => $application->open_key = null,
            'set_resume_stage' => $application->resume_stage = $param,
            'clear_resume_stage' => $application->resume_stage = null,
            'set_decided_at' => $application->decided_at = now(),
            default => throw new \LogicException("Unknown application field effect [{$this->field}]."),
        };
    }

    public function payloadRules(?string $param): array
    {
        return [];
    }
}
