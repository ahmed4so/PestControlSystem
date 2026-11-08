<?php

declare(strict_types=1);

namespace App\Domain\Applications\Support;

use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Inspection\Enums\InspectionStatus;
use App\Domain\Workflow\Enums\ReviewResult;
use App\Domain\Workflow\Enums\WorkflowActionCode;
use App\Models\Application;
use App\Models\Inspection;
use App\Models\ReviewItem;
use App\Models\WorkflowAction;

/**
 * What the company sees of the processing (D-201, D-045):
 * - status changes, with company labels;
 * - the comments of returns and DG deferrals;
 * - review items that are not compliant, with their comments.
 * Never internal comments, staff names, or inspection reports.
 */
final class CompanyApplicationView
{
    /** Actions whose comment the company sees. */
    public const PUBLIC_COMMENTS = [WorkflowActionCode::ReturnToCompany, WorkflowActionCode::Defer];

    /**
     * @return list<array{date: string, status: string, comment: string|null}>
     */
    public static function history(Application $application): array
    {
        $entries = [];

        $actions = WorkflowAction::query()
            ->where('application_id', $application->id)
            ->orderBy('id')
            ->get(['id', 'action', 'from_status', 'to_status', 'comment', 'created_at']);

        foreach ($actions as $action) {
            $from = ApplicationStatus::from($action->from_status)->companyLabel();
            $to = ApplicationStatus::from($action->to_status)->companyLabel();
            $public = in_array($action->action, self::PUBLIC_COMMENTS, true);

            if ($from === $to && ! $public) {
                continue;
            }

            $entries[] = [
                'date' => $action->created_at->toIso8601String(),
                'status' => $to,
                'comment' => $public ? $action->comment : null,
            ];
        }

        return $entries;
    }

    /**
     * Planned inspections of the latest round: premises and date only
     * (02 §4.8 company access, D-201); reports are never shown.
     *
     * @return list<array{premises: string, planned_date: string}>
     */
    public static function inspections(Application $application): array
    {
        $round = Inspection::query()->where('application_id', $application->id)->max('round');

        if ($round === null || ! in_array($application->status, [ApplicationStatus::InspectionScheduled, ApplicationStatus::InspectionReport], true)) {
            return [];
        }

        return Inspection::query()
            ->where('application_id', $application->id)
            ->where('round', $round)
            ->where('status', InspectionStatus::Scheduled)
            ->with('premises')
            ->orderBy('planned_date')
            ->get()
            ->map(fn (Inspection $inspection) => [
                'premises' => (string) $inspection->premises?->label,
                'planned_date' => $inspection->planned_date->format('Y-m-d'),
            ])
            ->values()
            ->all();
    }

    /**
     * On a returned application: the items to fix and the comment of the
     * return or deferral.
     *
     * @return array{comment: string|null, items: list<array{label: string, result: string, comment: string|null}>}|null
     */
    public static function returned(Application $application): ?array
    {
        if ($application->status !== ApplicationStatus::Returned) {
            return null;
        }

        $return = WorkflowAction::query()
            ->where('application_id', $application->id)
            ->whereIn('action', self::PUBLIC_COMMENTS)
            ->latest('id')
            ->first();

        $version = $application->currentVersion();
        $items = $version === null || $return === null ? [] : ReviewItem::query()
            ->where('application_version_id', $version->id)
            ->where('stage', $return->from_status)
            ->where('result', '!=', ReviewResult::Compliant)
            ->orderBy('id')
            ->get()
            ->map(fn (ReviewItem $item) => [
                'label' => ReviewItemLabels::label($item->item_code),
                'result' => $item->result === ReviewResult::Deficient ? 'Deficient' : 'Clarification required',
                'comment' => $item->comment,
            ])
            ->values()
            ->all();

        return ['comment' => $return?->comment, 'items' => $items];
    }
}
