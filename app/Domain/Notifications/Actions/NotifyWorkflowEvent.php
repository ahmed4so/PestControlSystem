<?php

declare(strict_types=1);

namespace App\Domain\Notifications\Actions;

use App\Domain\Applications\Support\ReviewItemLabels;
use App\Domain\Identity\Enums\AccountStatus;
use App\Domain\Notifications\Enums\NotificationCode;
use App\Domain\Workflow\Engine\TransitionContext;
use App\Domain\Workflow\Enums\ActorRole;
use App\Domain\Workflow\Enums\ReviewResult;
use App\Models\Application;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\ReviewItem;
use App\Models\StageAssignment;
use App\Models\User;
use App\Models\WorkflowStage;
use LogicException;

/**
 * Chooses who gets a workflow email and what it says (07 §2, §3.3; D-127,
 * D-198). Runs inside the transition's transaction, after the new status
 * is saved; the emails themselves are queued after commit (D-129). Staff
 * never get an email for their own action.
 */
final class NotifyWorkflowEvent
{
    /** Codes whose recipients are built (the others come with their phase, D-188). */
    public const BUILT = [
        NotificationCode::ApplicationSubmitted,
        NotificationCode::WorkItemReady,
        NotificationCode::ApplicationAcknowledged,
        NotificationCode::ApplicationReturned,
        NotificationCode::ApplicationResubmitted,
        NotificationCode::ReviewerAssigned,
        NotificationCode::TechnicalReviewDone,
    ];

    /**
     * Extra code handlers registered by later steps (inspection emails).
     *
     * @var array<string, \Closure(TransitionContext, array<string, mixed>, string): void>
     */
    private array $handlers = [];

    public function __construct(
        private readonly SendNotification $send,
        private readonly NotifyRole $notifyRole,
    ) {}

    /**
     * @param  \Closure(TransitionContext, array<string, mixed>, string): void  $handler
     */
    public function extend(NotificationCode $code, \Closure $handler): void
    {
        $this->handlers[$code->value] = $handler;
    }

    public function supports(NotificationCode $code): bool
    {
        return in_array($code, self::BUILT, true) || isset($this->handlers[$code->value]);
    }

    public function send(NotificationCode $code, TransitionContext $context, string $from): void
    {
        $application = $context->application;
        $data = $this->baseData($application);

        if (isset($this->handlers[$code->value])) {
            ($this->handlers[$code->value])($context, $data, $from);

            return;
        }

        match ($code) {
            NotificationCode::ApplicationSubmitted,
            NotificationCode::ApplicationAcknowledged => $this->toCompany($application, $code, $data),
            NotificationCode::ApplicationReturned => $this->toCompany($application, $code, [
                ...$data,
                'comment' => (string) $context->record?->comment,
                'items' => $this->returnedItems($context, $from),
            ]),
            NotificationCode::WorkItemReady => $application->status->value === $from
                ? null
                : $this->toStage($context, $code, $data),
            NotificationCode::ApplicationResubmitted => $this->toStage($context, $code, [
                ...$data,
                'version' => (string) $application->current_version_no,
            ]),
            NotificationCode::ReviewerAssigned => $this->toStaff(
                User::query()->find($context->payload('assignee_id')),
                $context,
                $code,
                $data,
            ),
            NotificationCode::TechnicalReviewDone => $this->toStaff(
                $this->assignerOfEndedReview($context),
                $context,
                $code,
                $data,
            ),
            default => throw new LogicException("The email [{$code->value}] is built in a later phase (D-188)."),
        };
    }

    /**
     * Values every workflow email may use.
     *
     * @return array<string, mixed>
     */
    public function baseData(Application $application): array
    {
        $company = Company::query()->withoutGlobalScope('company')->find($application->company_id);

        return [
            'reference' => (string) $application->reference,
            'service' => $application->service->label(),
            'category' => $application->license_category?->label() ?? '',
            'company' => (string) $company?->name,
            'company_url' => route('portal.applications.show', $application),
            'staff_url' => route('admin.applications.show', $application),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function toCompany(Application $application, NotificationCode $code, array $data): void
    {
        $accounts = CompanyUser::query()
            ->where('company_id', $application->company_id)
            ->where('status', AccountStatus::Active)
            ->get();

        foreach ($accounts as $account) {
            ($this->send)($account, $code, [...$data, 'url' => $data['company_url']], related: $application, reference: $application->reference);
        }
    }

    /**
     * Every active holder of the new stage's role, or only the assignees when
     * the stage is worked by assigned users (07 §2, D-127).
     *
     * @param  array<string, mixed>  $data
     */
    private function toStage(TransitionContext $context, NotificationCode $code, array $data): void
    {
        $application = $context->application;
        $stage = $context->template->stages->firstWhere('code', $application->status->value);

        if (! $stage instanceof WorkflowStage || $stage->is_final) {
            return;
        }

        $role = ActorRole::tryFrom($stage->actor_role);
        $data = [...$data, 'url' => $data['staff_url'], 'stage_en' => $stage->name_en, 'stage_so' => $stage->name_so];
        $actor = $context->staffUser();

        if ($role === null || $role === ActorRole::Company || $role === ActorRole::System) {
            return;
        }

        // Assigned stages: the assignee already got REVIEWER_ASSIGNED or
        // INSPECTION_SCHEDULED; never email every holder of the role (07 §2).
        if (in_array($role, [ActorRole::Inspector, ActorRole::TechnicalReviewer], true) && ! isset($this->handlers['assignees:'.$role->value])) {
            return;
        }

        if (isset($this->handlers['assignees:'.$role->value])) {
            // Inspector stages: the Inspection module names the assignees.
            ($this->handlers['assignees:'.$role->value])($context, ['code' => $code, ...$data], $application->status->value);

            return;
        }

        $staffRole = $role->staffRole();

        if ($staffRole !== null) {
            ($this->notifyRole)($staffRole, $code, $data, related: $application, actor: $actor, reference: $application->reference);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function toStaff(?User $user, TransitionContext $context, NotificationCode $code, array $data): void
    {
        if ($user === null || $user->status !== AccountStatus::Active || $user->is($context->staffUser())) {
            return;
        }

        ($this->send)($user, $code, [...$data, 'url' => $data['staff_url']], related: $context->application, reference: $context->application->reference);
    }

    /**
     * Registers who an assigned stage notifies (used for the Inspector).
     *
     * @param  \Closure(TransitionContext, array<string, mixed>, string): void  $handler
     */
    public function extendAssignees(ActorRole $role, \Closure $handler): void
    {
        $this->handlers['assignees:'.$role->value] = $handler;
    }

    /**
     * The Registrar who assigned the review that the actor just ended (T6).
     */
    private function assignerOfEndedReview(TransitionContext $context): ?User
    {
        $assignment = StageAssignment::query()
            ->where('application_id', $context->application->id)
            ->where('assigned_user_id', $context->actor->user->getKey())
            ->whereNotNull('ended_at')
            ->latest('ended_at')
            ->first();

        return $assignment === null ? null : User::query()->find($assignment->assigned_by);
    }

    /**
     * Items that are not compliant at the stage that returned the
     * application, with their comments (07 §3.3). A DG deferral has none;
     * its comment is shown instead.
     *
     * @return list<array{label: string, result: string, comment: string}>
     */
    private function returnedItems(TransitionContext $context, string $from): array
    {
        if ($context->version === null) {
            return [];
        }

        return ReviewItem::query()
            ->where('application_version_id', $context->version->id)
            ->where('stage', $from)
            ->where('result', '!=', ReviewResult::Compliant)
            ->orderBy('id')
            ->get()
            ->map(fn (ReviewItem $item) => [
                'label' => ReviewItemLabels::label($item->item_code),
                'result' => $item->result === ReviewResult::Deficient ? 'Deficient' : 'Clarification required',
                'comment' => (string) $item->comment,
            ])
            ->values()
            ->all();
    }
}
