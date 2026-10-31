<?php

declare(strict_types=1);

namespace App\Domain\Workflow\Guards;

use App\Domain\Workflow\Contracts\Guard;
use App\Domain\Workflow\Engine\GuardResult;
use App\Domain\Workflow\Engine\TransitionContext;
use App\Domain\Workflow\Enums\ReviewResult;
use App\Models\ReviewItem;

/**
 * The three review-item guards of 04 §11, on the current version and the
 * stage given as parameter (`ReviewItemsAllCompliant:ADMIN_REVIEW`):
 * - all: every item of the stage recorded and compliant;
 * - deficiency: at least one item deficient or needing clarification;
 * - recorded: every item of the stage recorded.
 */
final class ReviewItemsGuard implements Guard
{
    public const REASON_NOT_RECORDED = 'Record a result for every review item first.';

    public const REASON_NOT_COMPLIANT = 'Every review item must be compliant.';

    public const REASON_NO_DEFICIENCY = 'Mark at least one review item as deficient or needing clarification.';

    public function __construct(private readonly string $mode) {}

    public static function allCompliant(): self
    {
        return new self('all');
    }

    public static function haveDeficiency(): self
    {
        return new self('deficiency');
    }

    public static function recorded(): self
    {
        return new self('recorded');
    }

    public function check(TransitionContext $context, ?string $param): GuardResult
    {
        $stage = (string) $param;
        $expected = $context->reviewItemsOf($stage);
        $version = $context->version;

        $results = $version === null
            ? collect()
            : ReviewItem::query()
                ->where('application_version_id', $version->id)
                ->where('stage', $stage)
                ->get()
                ->keyBy('item_code');

        $notCompliant = $results->filter(fn (ReviewItem $item) => $item->result !== ReviewResult::Compliant);
        $allRecorded = array_diff($expected, $results->keys()->all()) === [];

        return match ($this->mode) {
            'deficiency' => $notCompliant->isNotEmpty() ? GuardResult::pass() : GuardResult::fail(self::REASON_NO_DEFICIENCY),
            'recorded' => $allRecorded ? GuardResult::pass() : GuardResult::fail(self::REASON_NOT_RECORDED),
            default => match (true) {
                ! $allRecorded => GuardResult::fail(self::REASON_NOT_RECORDED),
                $notCompliant->isNotEmpty() => GuardResult::fail(self::REASON_NOT_COMPLIANT),
                default => GuardResult::pass(),
            },
        };
    }

    public function needsPayload(): bool
    {
        return false;
    }
}
