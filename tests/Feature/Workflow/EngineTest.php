<?php

declare(strict_types=1);

use App\Domain\Applications\Actions\RecordReviewItems;
use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Applications\Enums\LicenseCategory;
use App\Domain\Workflow\Contracts\Effect;
use App\Domain\Workflow\Contracts\Guard;
use App\Domain\Workflow\Engine\GuardResult;
use App\Domain\Workflow\Engine\TransitionContext;
use App\Domain\Workflow\Engine\WorkflowEngine;
use App\Domain\Workflow\Enums\WorkflowActionCode;
use App\Domain\Workflow\Exceptions\AlreadyProcessed;
use App\Domain\Workflow\Exceptions\TransitionRefused;
use App\Domain\Workflow\Registry\WorkflowRegistry;
use App\Models\Application;
use App\Models\ApplicationVersion;
use App\Models\AuditActivity;
use App\Models\WorkflowAction;
use App\Models\WorkflowTemplate;
use Illuminate\Support\Facades\Mail;

/*
 * The engine (04 §4, D-096) on the small test definition.
 */

function workflowRows(Application $application): array
{
    return [
        'status' => Application::query()->withoutGlobalScopes()->findOrFail($application->id)->status->value,
        'actions' => WorkflowAction::query()->where('application_id', $application->id)->count(),
        'versions' => ApplicationVersion::query()->where('application_id', $application->id)->count(),
        'audit' => AuditActivity::query()->where('event', 'workflow_action')->count(),
    ];
}

it('submits a draft: reference, first version, template, action and audit entry', function () {
    $application = submittedApplication();
    $application->refresh();

    expect($application->status)->toBe(ApplicationStatus::Submitted)
        ->and($application->reference)->toMatch('/^CL-\d{4}-\d{5}$/')
        ->and($application->first_submitted_at)->not->toBeNull()
        ->and($application->current_version_no)->toBe(1)
        ->and($application->workflow_template_id)->toBe(WorkflowTemplate::query()->where('code', 'TEST_FLOW_V1')->value('id'));

    $version = $application->currentVersion();
    expect($version->declaration_name)->toBe('Amina Hassan')
        ->and($version->declaration_ip)->toBe('127.0.0.1')
        ->and($version->snapshot['schema_version'])->toBe(1);

    $action = WorkflowAction::query()->where('application_id', $application->id)->sole();
    expect($action->action)->toBe(WorkflowActionCode::Submit)
        ->and($action->from_status)->toBe('DRAFT')
        ->and($action->to_status)->toBe('SUBMITTED')
        ->and($action->actor_role)->toBe('COMPANY')
        ->and($action->application_version_id)->toBe($version->id);

    expect(AuditActivity::query()->where('event', 'workflow_action')->where('subject_id', $application->id)->exists())->toBeTrue();
});

it('runs the whole test flow to ISSUED and clears the open key', function () {
    $application = advanceTo(submittedApplication(), ApplicationStatus::DgDecision);

    act($application, WorkflowActionCode::Approve, staffWithRole('DIRECTOR_GENERAL'));
    $application->refresh();

    expect($application->status)->toBe(ApplicationStatus::Issued)
        ->and($application->open_key)->toBeNull()
        ->and($application->decided_at)->not->toBeNull()
        ->and(WorkflowAction::query()->where('application_id', $application->id)->count())->toBe(8);
});

it('keeps the stage for a "same" target', function () {
    $registrar = staffWithRole('REGISTRAR');
    $application = advanceTo(submittedApplication(), ApplicationStatus::TechnicalDocReview, $registrar);
    $reviewer = staffWithRole('TECHNICAL_REVIEWER');

    act($application, WorkflowActionCode::AssignTechnicalReviewer, $registrar, ['assignee_id' => $reviewer->id]);

    expect($application->status)->toBe(ApplicationStatus::TechnicalDocReview);
});

it('resolves the conditional target of T12 (D-043)', function () {
    $first = advanceTo(submittedApplication(), ApplicationStatus::ConsolidatedReview);
    act($first, WorkflowActionCode::CompleteConsolidatedReview, staffWithRole('REGISTRAR'));
    expect($first->status)->toBe(ApplicationStatus::DirectorReview);

    app(WorkflowRegistry::class)->extendGuard('PaymentCleared', fn () => new class implements Guard
    {
        public function check(TransitionContext $context, ?string $param): GuardResult
        {
            return GuardResult::pass();
        }

        public function needsPayload(): bool
        {
            return false;
        }
    });

    $second = Application::factory()->category(LicenseCategory::Retailer)->create();
    act($second, WorkflowActionCode::Submit, ownerOf($second), declaration());
    advanceTo($second, ApplicationStatus::ConsolidatedReview);
    act($second, WorkflowActionCode::CompleteConsolidatedReview, staffWithRole('REGISTRAR'));

    expect($second->status)->toBe(ApplicationStatus::DgDecision);
});

it('returns to the company and resubmits to the stage that returned it', function () {
    $registrar = staffWithRole('REGISTRAR');
    $application = advanceTo(submittedApplication(), ApplicationStatus::AdminReview, $registrar);

    app(RecordReviewItems::class)->handle($application, $registrar, 'ADMIN_REVIEW', [
        'ITEM_A' => ['result' => 'COMPLIANT'],
        'ITEM_B' => ['result' => 'DEFICIENT', 'comment' => 'The certificate is unreadable.'],
    ]);
    act($application, WorkflowActionCode::ReturnToCompany, $registrar, ['comment' => 'Please upload a readable certificate.']);
    $application->refresh();

    expect($application->status)->toBe(ApplicationStatus::Returned)
        ->and($application->resume_stage)->toBe('ADMIN_REVIEW');

    act($application, WorkflowActionCode::Resubmit, ownerOf($application), [...declaration(), 'response_note' => 'Uploaded again.']);
    $application->refresh();

    expect($application->status)->toBe(ApplicationStatus::AdminReview)
        ->and($application->resume_stage)->toBeNull()
        ->and($application->current_version_no)->toBe(2)
        ->and($application->currentVersion()->response_note)->toBe('Uploaded again.');
});

it('continues at consolidated review after a DG deferral (D-043)', function () {
    $application = advanceTo(submittedApplication(), ApplicationStatus::DgDecision);

    act($application, WorkflowActionCode::Defer, staffWithRole('DIRECTOR_GENERAL'), ['comment' => 'Clarify the storage address.']);
    act($application, WorkflowActionCode::Resubmit, ownerOf($application), declaration());

    expect($application->status)->toBe(ApplicationStatus::ConsolidatedReview);
});

it('needs a comment where the definition asks for one', function () {
    $application = advanceTo(submittedApplication(), ApplicationStatus::DgDecision);
    $before = workflowRows($application);

    expect(fn () => act($application, WorkflowActionCode::Defer, staffWithRole('DIRECTOR_GENERAL')))
        ->toThrow(TransitionRefused::class);

    expect(workflowRows($application))->toBe($before);
});

it('refuses an action that has no transition from the current stage', function () {
    $application = submittedApplication();

    expect(fn () => act($application, WorkflowActionCode::Approve, staffWithRole('DIRECTOR_GENERAL')))
        ->toThrow(TransitionRefused::class, WorkflowEngine::NO_TRANSITION);
});

it('refuses when no workflow is loaded', function () {
    $application = Application::factory()->create();

    expect(fn () => act($application, WorkflowActionCode::Submit, ownerOf($application), declaration()))
        ->toThrow(TransitionRefused::class, WorkflowEngine::NO_WORKFLOW);
});

it('says "already processed" when the status is not the one the user saw (04 §16 item 4)', function () {
    $application = submittedApplication();
    $registrar = staffWithRole('REGISTRAR');
    $before = workflowRows($application);

    expect(fn () => app(WorkflowEngine::class)->perform(
        $application, WorkflowActionCode::Acknowledge, workflowActor($registrar), ApplicationStatus::Draft,
    ))->toThrow(AlreadyProcessed::class);

    expect(workflowRows($application))->toBe($before);
});

it('lets only the first of two identical attempts succeed', function () {
    $application = submittedApplication();
    $stale = Application::query()->withoutGlobalScopes()->findOrFail($application->id);
    $registrar = staffWithRole('REGISTRAR');

    act($application, WorkflowActionCode::Acknowledge, $registrar);

    expect(fn () => app(WorkflowEngine::class)->perform(
        $stale, WorkflowActionCode::Acknowledge, workflowActor($registrar), $stale->status,
    ))->toThrow(AlreadyProcessed::class);

    expect(WorkflowAction::query()->where('application_id', $application->id)->where('action', 'ACKNOWLEDGE')->count())->toBe(1);
});

it('rolls back everything when an effect throws, and sends nothing (04 §16 item 5)', function () {
    Mail::fake();
    $registrar = staffWithRole('REGISTRAR');
    $application = advanceTo(submittedApplication(), ApplicationStatus::AdminReview, $registrar);
    app(RecordReviewItems::class)->handle($application, $registrar, 'ADMIN_REVIEW', [
        'ITEM_A' => ['result' => 'DEFICIENT', 'comment' => 'Missing.'], 'ITEM_B' => ['result' => 'COMPLIANT'],
    ]);
    act($application, WorkflowActionCode::ReturnToCompany, $registrar, ['comment' => 'Fix item A.']);

    app(WorkflowRegistry::class)->extendEffect('ClearResumeStage', fn () => new class implements Effect
    {
        public function apply(TransitionContext $context, ?string $param): void
        {
            throw new RuntimeException('boom');
        }

        public function payloadRules(?string $param): array
        {
            return [];
        }
    });

    $before = workflowRows($application);

    // CreateVersionSnapshot runs first and writes a version; the next effect throws.
    expect(fn () => act($application, WorkflowActionCode::Resubmit, ownerOf($application), declaration()))
        ->toThrow(RuntimeException::class, 'boom');

    expect(workflowRows($application))->toBe($before);
    $fresh = Application::query()->withoutGlobalScopes()->findOrFail($application->id);
    expect($fresh->resume_stage)->toBe('ADMIN_REVIEW')
        ->and($fresh->current_version_no)->toBe(1);
    Mail::assertNothingOutgoing();
});
