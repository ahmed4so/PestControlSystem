<?php

declare(strict_types=1);

namespace App\Domain\Applications\Actions;

use App\Domain\Workflow\Enums\ActorRole;
use App\Domain\Workflow\Enums\ReviewResult;
use App\Domain\Workflow\Exceptions\TransitionRefused;
use App\Models\Application;
use App\Models\ReviewItem;
use App\Models\StageAssignment;
use App\Models\User;
use App\Models\WorkflowStage;
use Illuminate\Support\Facades\DB;

/**
 * Records review-item results on the current version for the stage the
 * application is at (04 §5.3, 02 §4.7, D-024, D-102). Only the stage's
 * role may record; at TECHNICAL_DOC_REVIEW also the assigned Technical
 * Reviewer. A result that is not COMPLIANT needs a comment. Review items
 * keep their rows forever (Phase 3 plan §6): one result per item and
 * version, never changed or deleted. Whether a reviewer may correct a
 * result before leaving the stage is Proposed D-195.
 */
final class RecordReviewItems
{
    public const REASON_STAGE = 'Review items can only be recorded for the current stage.';

    public const REASON_ROLE = 'You cannot record review items at this stage.';

    public const REASON_ITEM = 'Unknown review item :item.';

    public const REASON_COMMENT = 'Add a comment for each item that is not compliant.';

    public const REASON_RECORDED = 'The result of :item is already recorded.';

    /**
     * @param  array<string, array{result: ReviewResult|string, comment?: string|null}>  $items
     */
    public function handle(Application $application, User $reviewer, string $stage, array $items): void
    {
        if ($application->status->value !== $stage || $application->workflow_template_id === null) {
            throw TransitionRefused::because(self::REASON_STAGE);
        }

        $stageRow = WorkflowStage::query()
            ->where('workflow_template_id', $application->workflow_template_id)
            ->where('code', $stage)
            ->first();

        if ($stageRow === null || ! $this->mayRecord($application, $reviewer, $stageRow)) {
            throw TransitionRefused::because(self::REASON_ROLE);
        }

        $allowed = $stageRow->review_items ?? [];
        $version = $application->currentVersion() ?? throw TransitionRefused::because(self::REASON_STAGE);

        foreach ($items as $code => $item) {
            if (! in_array($code, $allowed, true)) {
                throw TransitionRefused::because(__(self::REASON_ITEM, ['item' => $code]));
            }

            $result = $item['result'] instanceof ReviewResult ? $item['result'] : ReviewResult::from($item['result']);

            if ($result !== ReviewResult::Compliant && blank($item['comment'] ?? null)) {
                throw TransitionRefused::because(self::REASON_COMMENT);
            }
        }

        $recorded = ReviewItem::query()
            ->where('application_version_id', $version->id)
            ->where('stage', $stage)
            ->whereIn('item_code', array_keys($items))
            ->value('item_code');

        if ($recorded !== null) {
            throw TransitionRefused::because(__(self::REASON_RECORDED, ['item' => $recorded]));
        }

        DB::transaction(function () use ($items, $version, $stage, $reviewer): void {
            foreach ($items as $code => $item) {
                $row = new ReviewItem([
                    'stage' => $stage,
                    'item_code' => $code,
                    'result' => $item['result'],
                    'comment' => $item['comment'] ?? null,
                    'reviewed_by' => $reviewer->id,
                    'reviewed_at' => now(),
                ]);
                $row->version()->associate($version);
                $row->save();
            }
        });
    }

    private function mayRecord(Application $application, User $reviewer, WorkflowStage $stage): bool
    {
        $role = ActorRole::tryFrom($stage->actor_role)?->staffRole();

        if ($role !== null && $reviewer->hasRole($role->value)) {
            return true;
        }

        return $stage->code === 'TECHNICAL_DOC_REVIEW'
            && StageAssignment::query()
                ->where('application_id', $application->id)
                ->where('stage', $stage->code)
                ->where('assigned_user_id', $reviewer->id)
                ->open()
                ->exists();
    }
}
