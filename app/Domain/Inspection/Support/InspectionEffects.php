<?php

declare(strict_types=1);

namespace App\Domain\Inspection\Support;

use App\Domain\Inspection\Enums\InspectionStatus;
use App\Domain\Workflow\Contracts\Effect;
use App\Domain\Workflow\Engine\TransitionContext;
use App\Domain\Workflow\Exceptions\TransitionRefused;
use App\Models\Inspection;
use LogicException;

/**
 * The inspection effects of 04 §12 (D-077, D-099, D-196):
 * - create: one inspection per submitted premises, in a new round (T7, T14);
 * - reschedule: a new planned date for an inspection not yet visited (T9);
 * - visit: the Inspector records the visit to their inspection (T10);
 * - sign: the Inspector signs the report, which locks it (T11);
 * - cancel: open inspections are cancelled.
 */
final class InspectionEffects implements Effect
{
    public const REASON_VISITED = 'This inspection was already visited, so its date cannot change.';

    public function __construct(private readonly string $mode) {}

    public function apply(TransitionContext $context, ?string $param): void
    {
        match ($this->mode) {
            'create' => $this->create($context),
            'reschedule' => $this->reschedule($context),
            'visit' => $this->visit($context),
            'sign' => $this->sign($context),
            'cancel' => Inspection::query()->where('application_id', $context->application->id)->open()->get()
                ->each(fn (Inspection $inspection) => $inspection->forceFill(['status' => InspectionStatus::Cancelled])->save()),
            default => throw new LogicException("Unknown inspection effect [{$this->mode}]."),
        };
    }

    public function payloadRules(?string $param): array
    {
        return match ($this->mode) {
            'create' => [
                'inspections' => ['required', 'array', 'min:1'],
                'inspections.*.premises_id' => ['required', 'integer'],
                'inspections.*.inspector_id' => ['required', 'integer'],
                'inspections.*.planned_date' => ['required', 'date_format:Y-m-d'],
            ],
            'reschedule' => [
                'inspection_id' => ['required', 'integer'],
                'planned_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            ],
            'visit', 'sign' => ['inspection_id' => ['required', 'integer']],
            default => [],
        };
    }

    private function create(TransitionContext $context): void
    {
        $application = $context->application;
        $version = $context->version ?? throw new LogicException('An inspection needs a submitted version.');
        $round = (int) Inspection::query()->where('application_id', $application->id)->max('round') + 1;

        foreach ((array) $context->payload('inspections', []) as $row) {
            $inspection = new Inspection;
            $inspection->forceFill([
                'application_id' => $application->id,
                'application_version_id' => $version->id,
                'premises_id' => (int) $row['premises_id'],
                'inspector_id' => (int) $row['inspector_id'],
                'scheduled_by' => (int) $context->actor->user->getKey(),
                'round' => $round,
                'planned_date' => $row['planned_date'],
                'status' => InspectionStatus::Scheduled,
            ])->save();
        }
    }

    private function reschedule(TransitionContext $context): void
    {
        $inspection = InspectionGuards::fromPayload($context) ?? throw TransitionRefused::because(InspectionGuards::REASON_UNKNOWN);

        if ($inspection->conducted_at !== null) {
            throw TransitionRefused::because(self::REASON_VISITED);
        }

        $inspection->forceFill(['planned_date' => $context->payload('planned_date')])->save();
    }

    private function visit(TransitionContext $context): void
    {
        $inspection = InspectionGuards::fromPayload($context) ?? throw TransitionRefused::because(InspectionGuards::REASON_UNKNOWN);

        if ($inspection->conducted_at === null) {
            $inspection->forceFill(['conducted_at' => now()])->save();
        }
    }

    private function sign(TransitionContext $context): void
    {
        $inspection = InspectionGuards::fromPayload($context) ?? throw TransitionRefused::because(InspectionGuards::REASON_UNKNOWN);

        $inspection->forceFill(['signed_at' => now(), 'status' => InspectionStatus::Completed])->save();
    }
}
