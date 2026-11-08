<?php

declare(strict_types=1);

namespace App\Domain\Inspection\Support;

use App\Domain\Identity\Enums\AccountStatus;
use App\Domain\Identity\Enums\StaffRole;
use App\Domain\Inspection\Enums\InspectionItemResult;
use App\Domain\Inspection\Enums\InspectionStatus;
use App\Domain\Inspection\Enums\MediaType;
use App\Domain\Workflow\Contracts\Guard;
use App\Domain\Workflow\Engine\GuardResult;
use App\Domain\Workflow\Engine\TransitionContext;
use App\Models\Inspection;
use App\Models\User;

/**
 * The inspection guards of 04 §11 (procedure 6.5–6.7, D-077, D-136):
 * - plan: one inspection per submitted premises, each with an active
 *   Inspector and a planned date from today (05 §6);
 * - report: the inspection named in the payload is visited and its report
 *   complete;
 * - signed: every inspection of the latest round is signed.
 */
final class InspectionGuards implements Guard
{
    public const REASON_PLAN = 'Plan one inspection for each premises of the application.';

    public const REASON_INSPECTOR = 'Choose an active Inspector for each inspection.';

    public const REASON_DATE = 'The planned date must be today or later.';

    public const REASON_NOT_VISITED = 'Record the inspection visit first.';

    public const REASON_ITEMS = 'Answer every checklist item.';

    public const REASON_FACTS = 'Record the observed facts.';

    public const REASON_DEFICIENCIES = 'Describe the deficiencies found.';

    public const REASON_PHOTOS = 'Add at least one photo for each non-compliant item.';

    public const REASON_GPS = 'Record the GPS position, or the reason it is not available.';

    public const REASON_RESULT = 'Choose the overall result.';

    public const REASON_NOT_SIGNED = 'Every inspection must be signed first.';

    public const REASON_UNKNOWN = 'Choose one of your open inspections of this application.';

    public function __construct(private readonly string $mode) {}

    public static function plan(): self
    {
        return new self('plan');
    }

    public static function report(): self
    {
        return new self('report');
    }

    public static function signed(): self
    {
        return new self('signed');
    }

    public function check(TransitionContext $context, ?string $param): GuardResult
    {
        return match ($this->mode) {
            'plan' => $this->checkPlan($context),
            'report' => $this->checkReport($context),
            default => self::allSigned($context->application->id) ? GuardResult::pass() : GuardResult::fail(self::REASON_NOT_SIGNED),
        };
    }

    public function needsPayload(): bool
    {
        return $this->mode !== 'signed';
    }

    /**
     * Every inspection of the latest round (not cancelled) is signed.
     */
    public static function allSigned(int $applicationId): bool
    {
        $round = Inspection::query()->where('application_id', $applicationId)->max('round');

        if ($round === null) {
            return false;
        }

        return ! Inspection::query()
            ->where('application_id', $applicationId)
            ->where('round', $round)
            ->where('status', '!=', InspectionStatus::Cancelled)
            ->whereNull('signed_at')
            ->exists();
    }

    /**
     * The submitted premises of the current version (02 §6.3).
     *
     * @return list<int>
     */
    public static function submittedPremises(TransitionContext $context): array
    {
        $premises = (array) ($context->version?->snapshot['premises'] ?? []);

        return array_values(array_map(fn ($item) => (int) ($item['id'] ?? 0), $premises));
    }

    /**
     * The open inspection of this application named in the payload.
     */
    public static function fromPayload(TransitionContext $context): ?Inspection
    {
        $id = $context->payload('inspection_id');

        return is_numeric($id)
            ? Inspection::query()->where('application_id', $context->application->id)->open()->find((int) $id)
            : null;
    }

    private function checkPlan(TransitionContext $context): GuardResult
    {
        $plan = (array) $context->payload('inspections', []);
        $planned = array_map(fn ($row) => (int) ($row['premises_id'] ?? 0), $plan);
        $expected = self::submittedPremises($context);
        sort($planned);
        sort($expected);

        $reasons = [];

        if ($expected === [] || $planned !== $expected) {
            $reasons[] = self::REASON_PLAN;
        }

        $today = now()->setTimezone((string) config('app.display_timezone'))->toDateString();

        foreach ($plan as $row) {
            $inspector = User::query()->find($row['inspector_id'] ?? null);

            if (! $inspector instanceof User || $inspector->status !== AccountStatus::Active || ! $inspector->hasRole(StaffRole::Inspector->value)) {
                $reasons[] = self::REASON_INSPECTOR;
            }

            if ((string) ($row['planned_date'] ?? '') < $today) {
                $reasons[] = self::REASON_DATE;
            }
        }

        return GuardResult::failIfAny(array_values(array_unique($reasons)));
    }

    private function checkReport(TransitionContext $context): GuardResult
    {
        $inspection = self::fromPayload($context);

        if ($inspection === null) {
            return GuardResult::fail(self::REASON_UNKNOWN);
        }

        if ($inspection->conducted_at === null) {
            return GuardResult::fail(self::REASON_NOT_VISITED);
        }

        $reasons = [];
        $answers = $inspection->items()->get()->keyBy('item_code');
        $checklist = array_keys(InspectionChecklist::items($context->application->service));

        if (array_diff($checklist, $answers->keys()->all()) !== []) {
            $reasons[] = self::REASON_ITEMS;
        }

        if (blank($inspection->observed_facts)) {
            $reasons[] = self::REASON_FACTS;
        }

        $nonCompliant = $answers->filter(fn ($item) => $item->result === InspectionItemResult::NonCompliant);

        if ($nonCompliant->isNotEmpty() && blank($inspection->deficiencies)) {
            $reasons[] = self::REASON_DEFICIENCIES;
        }

        foreach ($nonCompliant as $item) {
            $photos = $inspection->media()->where('inspection_item_id', $item->id)->where('media_type', MediaType::Photo)->count();

            if ($photos === 0) {
                $reasons[] = self::REASON_PHOTOS;
                break;
            }
        }

        if (($inspection->gps_lat === null || $inspection->gps_lng === null) && blank($inspection->gps_unavailable_reason)) {
            $reasons[] = self::REASON_GPS;
        }

        if ($inspection->overall_result === null) {
            $reasons[] = self::REASON_RESULT;
        }

        return GuardResult::failIfAny($reasons);
    }
}
