<?php

declare(strict_types=1);

namespace App\Domain\Workflow\Engine;

use App\Models\Application;
use App\Models\ApplicationVersion;
use App\Models\User;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowTransition;

/**
 * Everything guards and effects see while one transition runs (04 §4).
 * Effects may change the application, set the version they create and say
 * who a delegate acts for.
 */
final class TransitionContext
{
    /**
     * @param  array<string, mixed>  $payload  input for this action (validated before guards run)
     */
    public function __construct(
        public readonly Application $application,
        public readonly WorkflowTemplate $template,
        public readonly WorkflowTransition $transition,
        public readonly WorkflowActor $actor,
        public array $payload,
        public ?ApplicationVersion $version,
        public ?User $onBehalfOf = null,
    ) {}

    public function staffUser(): ?User
    {
        return $this->actor->user instanceof User ? $this->actor->user : null;
    }

    public function payload(string $key, mixed $default = null): mixed
    {
        return $this->payload[$key] ?? $default;
    }

    /**
     * The review items configured for a stage of this template (04 §5.3).
     *
     * @return list<string>
     */
    public function reviewItemsOf(string $stage): array
    {
        $stageRow = $this->template->stages->firstWhere('code', $stage);

        return $stageRow->review_items ?? [];
    }
}
