<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Applications\Actions\RecordReviewItems;
use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Applications\Support\ReviewItemLabels;
use App\Domain\Applications\Support\SnapshotPresenter;
use App\Domain\Applications\Support\VersionDiff;
use App\Domain\Audit\Support\AuditLog;
use App\Domain\Documents\Services\FileStorage;
use App\Domain\Identity\Enums\AccountStatus;
use App\Domain\Identity\Enums\StaffRole;
use App\Domain\Workflow\Engine\WorkflowActor;
use App\Domain\Workflow\Engine\WorkflowEngine;
use App\Domain\Workflow\Enums\ReviewResult;
use App\Domain\Workflow\Enums\WorkflowActionCode;
use App\Domain\Workflow\Exceptions\AlreadyProcessed;
use App\Domain\Workflow\Exceptions\TransitionRefused;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationVersion;
use App\Models\CompanyUser;
use App\Models\ReviewItem;
use App\Models\StageAssignment;
use App\Models\StoredFile;
use App\Models\User;
use App\Models\WorkflowAction;
use App\Models\WorkflowTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * The application file for staff (04 §15, D-042, 09 Phase 4A row D):
 * the current version's snapshot (reviewers work only from it, 02 §6.3),
 * earlier versions with their differences (D-199), review items, the full
 * timeline, and the actions the user may take now with blocked reasons
 * (D-191). Every action goes through the workflow engine (D-096).
 */
class ApplicationController extends Controller
{
    /** Input passed to the engine; effects validate what they need. */
    private const PAYLOAD_KEYS = ['comment', 'assignee_id', 'inspections', 'inspection_id', 'planned_date'];

    public function show(Request $request, Application $application, WorkflowEngine $engine): Response
    {
        Gate::authorize('view', $application);

        /** @var User $user */
        $user = $request->user();
        $application->load(['company', 'template.stages']);
        $versions = $application->versions()->get();
        $current = $versions->last();

        return Inertia::render('admin/applications/show', [
            'application' => [
                'id' => $application->id,
                'reference' => $application->reference,
                'service' => $application->service->label(),
                'category' => $application->license_category?->label(),
                'type' => $application->application_type->value,
                'status' => $application->status->value,
                'stage' => $this->stageName($application->template, $application->status->value),
                'resume_stage' => $application->resume_stage,
                'company' => ['id' => $application->company_id, 'name' => $application->company?->name],
                'first_submitted_at' => $application->first_submitted_at?->toIso8601String(),
            ],
            'snapshot' => $current === null ? null : SnapshotPresenter::forStaff($current->snapshot),
            'versions' => $versions->values()->map(fn (ApplicationVersion $version, int $index) => [
                'version_no' => $version->version_no,
                'submitted_at' => $version->submitted_at->toIso8601String(),
                'declaration' => $version->declaration_name.' ('.$version->declaration_designation.')',
                'response_note' => $version->response_note,
                'changes' => $index === 0 ? [] : VersionDiff::between($versions[$index - 1]->snapshot, $version->snapshot),
            ]),
            'reviewItems' => $this->reviewItemProps($application, $current, $user),
            'assignments' => StageAssignment::query()->where('application_id', $application->id)->orderBy('id')->get()
                ->map(fn (StageAssignment $assignment) => [
                    'stage' => $assignment->stage,
                    'user' => User::query()->find($assignment->assigned_user_id)?->name,
                    'assigned_at' => $assignment->assigned_at->toIso8601String(),
                    'ended_at' => $assignment->ended_at?->toIso8601String(),
                ]),
            'timeline' => $this->timeline($application),
            'actions' => array_map(fn (array $action) => [
                ...$action,
                'label' => WorkflowActionCode::from($action['action'])->label(),
                'blocked_by' => array_map(fn (string $reason) => __($reason), $action['blocked_by']),
            ], $engine->availableActions($application, $user)),
            'technicalReviewers' => $this->technicalReviewers($application, $engine, $user),
        ]);
    }

    /**
     * Performs one workflow action (staff transitions of 04 §5.2).
     */
    public function perform(Request $request, Application $application, WorkflowEngine $engine): RedirectResponse
    {
        Gate::authorize('view', $application);

        $validated = $request->validate([
            'action' => ['required', Rule::enum(WorkflowActionCode::class)],
            'expected_status' => ['required', Rule::enum(ApplicationStatus::class)],
            'password' => ['nullable', 'string', 'current_password:web'],
        ]);

        // A password typed with the action confirms it (D-085).
        if (filled($validated['password'] ?? null)) {
            $request->session()->put('auth.password_confirmed_at', time());
        }

        try {
            $engine->perform(
                $application,
                WorkflowActionCode::from($validated['action']),
                WorkflowActor::fromRequest($request),
                $validated['expected_status'],
                $request->only(self::PAYLOAD_KEYS),
            );
        } catch (TransitionRefused $refused) {
            return back()->withErrors(['action' => implode(' ', array_map(fn (string $reason) => __($reason), $refused->reasons))]);
        } catch (AlreadyProcessed) {
            return back()->with('error', __(AlreadyProcessed::MESSAGE));
        }

        return back()->with('success', __('Done.'));
    }

    public function reviewItems(Request $request, Application $application, RecordReviewItems $record): RedirectResponse
    {
        Gate::authorize('view', $application);

        $validated = $request->validate([
            'stage' => ['required', 'string', 'max:40'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.result' => ['nullable', Rule::enum(ReviewResult::class)],
            'items.*.comment' => ['nullable', 'string', 'max:2000'],
        ]);

        /** @var User $user */
        $user = $request->user();

        try {
            // Items left blank are not recorded yet.
            $items = array_filter($validated['items'], fn (array $item) => filled($item['result'] ?? null));
            $record->handle($application, $user, $validated['stage'], $items);
        } catch (TransitionRefused $refused) {
            return back()->withErrors(['items' => implode(' ', array_map(fn (string $reason) => __($reason), $refused->reasons))]);
        }

        return back()->with('success', __('Saved.'));
    }

    /**
     * Opens a file referenced by one of the application's version snapshots;
     * every view is audited (D-055, D-181).
     */
    public function file(Request $request, Application $application, StoredFile $storedFile, FileStorage $files): HttpResponse
    {
        Gate::authorize('view', $application);

        $referenced = $application->versions()->get()
            ->contains(fn (ApplicationVersion $version) => in_array($storedFile->id, SnapshotPresenter::storedFileIds($version->snapshot), true));

        abort_unless($referenced, 404);

        AuditLog::record('application_file_viewed', 'Staff opened an application file', $application, $request->user(), [
            'stored_file_id' => $storedFile->id,
        ]);

        return $files->download($storedFile);
    }

    /**
     * Items of the current stage (to record) and every recorded result.
     *
     * @return array<string, mixed>
     */
    private function reviewItemProps(Application $application, ?ApplicationVersion $current, User $user): array
    {
        $stage = $application->template?->stages->firstWhere('code', $application->status->value);
        $recorded = $current === null ? collect() : ReviewItem::query()->where('application_version_id', $current->id)->orderBy('id')->get();

        return [
            'stage' => $stage?->review_items ? $stage->code : null,
            'items' => collect($stage->review_items ?? [])->map(fn (string $code) => [
                'code' => $code,
                'label' => ReviewItemLabels::label($code),
            ])->values(),
            'recorded' => $recorded->map(fn (ReviewItem $item) => [
                'stage' => $item->stage,
                'code' => $item->item_code,
                'label' => ReviewItemLabels::label($item->item_code),
                'result' => $item->result->value,
                'comment' => $item->comment,
                'mine' => $item->reviewed_by === $user->id,
                'reviewer' => User::query()->find($item->reviewed_by)?->name,
                'reviewed_at' => $item->reviewed_at->toIso8601String(),
            ])->values(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function timeline(Application $application): array
    {
        return WorkflowAction::query()
            ->where('application_id', $application->id)
            ->with(['actor', 'onBehalfOf'])
            ->orderBy('id')
            ->get()
            ->map(function (WorkflowAction $action) {
                $actor = $action->actor;

                return [
                    'id' => $action->id,
                    'action' => $action->action->value,
                    'from' => $action->from_status,
                    'to' => $action->to_status,
                    'actor' => $actor instanceof User ? $actor->name : ($actor instanceof CompanyUser ? $actor->full_name : null),
                    'role' => $action->actor_role,
                    'on_behalf_of' => $action->onBehalfOf?->name,
                    'comment' => $action->comment,
                    'at' => $action->created_at->toIso8601String(),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Active Technical Reviewers, offered when the assignment action is
     * available.
     *
     * @return list<array{id: int, name: string}>
     */
    private function technicalReviewers(Application $application, WorkflowEngine $engine, User $user): array
    {
        $canAssign = collect($engine->availableActions($application, $user))
            ->contains('action', WorkflowActionCode::AssignTechnicalReviewer->value);

        if (! $canAssign) {
            return [];
        }

        return User::role(StaffRole::TechnicalReviewer->value)
            ->where('status', AccountStatus::Active)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (User $reviewer) => ['id' => $reviewer->id, 'name' => $reviewer->name])
            ->values()
            ->all();
    }

    private function stageName(?WorkflowTemplate $template, string $code): string
    {
        $stage = $template?->stages->firstWhere('code', $code);

        if ($stage === null) {
            return $code;
        }

        return app()->getLocale() === 'so' ? $stage->name_so : $stage->name_en;
    }
}
