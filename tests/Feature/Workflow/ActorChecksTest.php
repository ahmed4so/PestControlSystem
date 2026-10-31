<?php

declare(strict_types=1);

use App\Domain\Applications\Actions\RecordReviewItems;
use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Identity\Enums\AccountStatus;
use App\Domain\Identity\Enums\StaffRole;
use App\Domain\Workflow\Checks\AssignmentCheck;
use App\Domain\Workflow\Checks\DelegationCheck;
use App\Domain\Workflow\Checks\PasswordCheck;
use App\Domain\Workflow\Checks\RealmCheck;
use App\Domain\Workflow\Checks\RoleCheck;
use App\Domain\Workflow\Checks\SegregationRules;
use App\Domain\Workflow\Engine\WorkflowActor;
use App\Domain\Workflow\Engine\WorkflowEngine;
use App\Domain\Workflow\Enums\WorkflowActionCode;
use App\Domain\Workflow\Exceptions\TransitionRefused;
use App\Domain\Workflow\Guards\ReviewItemsGuard;
use App\Models\Application;
use App\Models\Delegation;
use App\Models\StageAssignment;
use App\Models\WorkflowAction;

/*
 * Actor checks in the order of 04 §10 (04 §16 item 2). Every refusal gives
 * its reason and changes nothing.
 */

function refusedWith(Closure $attempt, string $reason): void
{
    try {
        $attempt();
    } catch (TransitionRefused $refused) {
        expect($refused->reasons)->toContain($reason);

        return;
    }

    test()->fail("Expected a refusal: {$reason}");
}

function actionCount(Application $application): int
{
    return WorkflowAction::query()->where('application_id', $application->id)->count();
}

it('refuses a staff user without the stage role', function () {
    $application = submittedApplication();

    refusedWith(fn () => act($application, WorkflowActionCode::Acknowledge, staffWithRole('PAYMENT_OFFICER')), RoleCheck::REASON);

    expect($application->fresh()->status)->toBe(ApplicationStatus::Submitted)
        ->and(actionCount($application))->toBe(1);
});

it('refuses the System Administrator, who has no workflow role (D-086)', function () {
    $application = submittedApplication();

    refusedWith(fn () => act($application, WorkflowActionCode::Acknowledge, systemAdministrator()), RoleCheck::REASON);
});

it('refuses a company account on a staff action', function () {
    $application = submittedApplication();

    refusedWith(fn () => act($application, WorkflowActionCode::Acknowledge, ownerOf($application)), RealmCheck::REASON);
    expect(actionCount($application))->toBe(1);
});

it('refuses a staff user on a company action', function () {
    loadTestWorkflow();
    $application = Application::factory()->create();

    refusedWith(fn () => act($application, WorkflowActionCode::Submit, staffWithRole('REGISTRAR'), declaration()), RealmCheck::REASON);
    expect($application->fresh()->status)->toBe(ApplicationStatus::Draft);
});

it('refuses another company\'s account', function () {
    loadTestWorkflow();
    $application = Application::factory()->create();

    refusedWith(fn () => act($application, WorkflowActionCode::Submit, companyUser(), declaration()), RealmCheck::REASON);
    expect($application->fresh()->status)->toBe(ApplicationStatus::Draft);
});

it('refuses an account that is not active', function () {
    $application = submittedApplication();
    $registrar = staffWithRole('REGISTRAR');
    $registrar->forceFill(['status' => AccountStatus::Inactive])->save();

    refusedWith(fn () => act($application, WorkflowActionCode::Acknowledge, $registrar), RealmCheck::REASON);
});

it('needs an open assignment for Technical Reviewer actions', function () {
    $registrar = staffWithRole('REGISTRAR');
    $application = advanceTo(submittedApplication(), ApplicationStatus::TechnicalDocReview, $registrar);
    $reviewer = staffWithRole('TECHNICAL_REVIEWER');

    refusedWith(fn () => act($application, WorkflowActionCode::CompleteTechnicalReview, $reviewer), AssignmentCheck::REASON);

    act($application, WorkflowActionCode::AssignTechnicalReviewer, $registrar, ['assignee_id' => $reviewer->id]);
    app(RecordReviewItems::class)->handle($application, $reviewer, 'TECHNICAL_DOC_REVIEW', ['ITEM_T' => ['result' => 'COMPLIANT']]);
    act($application, WorkflowActionCode::CompleteTechnicalReview, $reviewer);

    expect(StageAssignment::query()->where('application_id', $application->id)->sole()->ended_at)->not->toBeNull();

    // The assignment has ended: the reviewer cannot act again.
    refusedWith(fn () => act($application, WorkflowActionCode::CompleteTechnicalReview, $reviewer), AssignmentCheck::REASON);
});

it('only assigns a user who holds the Technical Reviewer role', function () {
    $registrar = staffWithRole('REGISTRAR');
    $application = advanceTo(submittedApplication(), ApplicationStatus::TechnicalDocReview, $registrar);

    expect(fn () => act($application, WorkflowActionCode::AssignTechnicalReviewer, $registrar, ['assignee_id' => staffWithRole('INSPECTOR')->id]))
        ->toThrow(TransitionRefused::class);
    expect(StageAssignment::query()->count())->toBe(0);
});

it('lets a delegate act for the DG and records on whose behalf (D-027)', function () {
    $application = advanceTo(submittedApplication(), ApplicationStatus::DgDecision);
    $director = staffWithRole('DIRECTOR_GENERAL');
    $delegate = staffWithRole('DEPARTMENTAL_DIRECTOR');
    Delegation::factory()->create(['delegator_id' => $director->id, 'delegate_id' => $delegate->id]);

    $action = act($application, WorkflowActionCode::Approve, $delegate);

    expect($action->on_behalf_of_id)->toBe($director->id)
        ->and($action->actor_id)->toBe($delegate->id)
        ->and($application->status)->toBe(ApplicationStatus::Issued);
});

it('refuses a delegate whose delegation expired or was revoked', function (string $state) {
    $application = advanceTo(submittedApplication(), ApplicationStatus::DgDecision);
    $director = staffWithRole('DIRECTOR_GENERAL');
    $delegate = staffWithRole('REGISTRAR');
    Delegation::factory()->{$state}()->create(['delegator_id' => $director->id, 'delegate_id' => $delegate->id]);

    refusedWith(fn () => act($application, WorkflowActionCode::Approve, $delegate), DelegationCheck::REASON);
    expect($application->fresh()->status)->toBe(ApplicationStatus::DgDecision);
})->with(['expired', 'revoked']);

it('refuses a decision by the user who recommended (D-086)', function () {
    $application = advanceTo(submittedApplication(), ApplicationStatus::DirectorReview);
    $both = staffUser([StaffRole::DepartmentalDirector->value, StaffRole::DirectorGeneral->value]);

    act($application, WorkflowActionCode::Recommend, $both);
    act($application, WorkflowActionCode::ClearPayment, staffWithRole('PAYMENT_OFFICER'));

    refusedWith(fn () => act($application, WorkflowActionCode::Approve, $both), SegregationRules::REASON_RECOMMENDED);
    refusedWith(fn () => act($application, WorkflowActionCode::Reject, $both, ['comment' => 'No.']), SegregationRules::REASON_RECOMMENDED);
    expect($application->fresh()->status)->toBe(ApplicationStatus::DgDecision);
});

it('refuses a decision by the user who cleared the payment (D-086)', function () {
    $application = advanceTo(submittedApplication(), ApplicationStatus::PaymentVerification);
    $both = staffUser([StaffRole::PaymentOfficer->value, StaffRole::DirectorGeneral->value]);

    act($application, WorkflowActionCode::ClearPayment, $both);

    refusedWith(fn () => act($application, WorkflowActionCode::Defer, $both, ['comment' => 'Later.']), SegregationRules::REASON_CLEARED);
});

it('refuses the permission-to-pay letter to a user who recorded review items (D-086)', function () {
    $registrar = staffWithRole('REGISTRAR');
    $application = advanceTo(submittedApplication(), ApplicationStatus::AdminReview, $registrar);
    app(RecordReviewItems::class)->handle($application, $registrar, 'ADMIN_REVIEW', ['ITEM_A' => ['result' => 'COMPLIANT']]);

    expect(SegregationRules::breach($application, $registrar, WorkflowActionCode::IssuePermissionToPay))
        ->toBe(SegregationRules::REASON_REVIEWED)
        ->and(SegregationRules::breach($application, staffWithRole('PAYMENT_OFFICER'), WorkflowActionCode::IssuePermissionToPay))
        ->toBeNull();
});

it('needs a recent password confirmation where the definition asks for one (D-085)', function () {
    loadTestWorkflow();
    $application = Application::factory()->create();
    $owner = ownerOf($application);

    refusedWith(fn () => act($application, WorkflowActionCode::Submit, $owner, declaration(), passwordConfirmed: false), PasswordCheck::REASON);

    $longAgo = new WorkflowActor($owner, now()->subMinutes(31)->getTimestamp());
    refusedWith(fn () => app(WorkflowEngine::class)->perform($application, WorkflowActionCode::Submit, $longAgo, 'DRAFT', declaration()), PasswordCheck::REASON);

    expect($application->fresh()->status)->toBe(ApplicationStatus::Draft)
        ->and($application->fresh()->reference)->toBeNull();
});

it('lists the actions a user can take, with the reasons they are blocked (D-191)', function () {
    $registrar = staffWithRole('REGISTRAR');
    $application = advanceTo(submittedApplication(), ApplicationStatus::AdminReview, $registrar);
    $engine = app(WorkflowEngine::class);

    $actions = collect($engine->availableActions($application, $registrar))->keyBy('action');
    expect($actions->keys()->all())->toEqualCanonicalizing(['COMPLETE_ADMIN_REVIEW', 'RETURN_TO_COMPANY'])
        ->and($actions['COMPLETE_ADMIN_REVIEW']['blocked_by'])->toBe([ReviewItemsGuard::REASON_NOT_RECORDED])
        ->and($actions['RETURN_TO_COMPANY']['blocked_by'])->toBe([ReviewItemsGuard::REASON_NO_DEFICIENCY])
        ->and($actions['RETURN_TO_COMPANY']['requires_comment'])->toBeTrue();

    app(RecordReviewItems::class)->handle($application, $registrar, 'ADMIN_REVIEW', [
        'ITEM_A' => ['result' => 'COMPLIANT'], 'ITEM_B' => ['result' => 'COMPLIANT'],
    ]);
    $actions = collect($engine->availableActions($application->fresh(), $registrar))->keyBy('action');
    expect($actions['COMPLETE_ADMIN_REVIEW']['blocked_by'])->toBe([]);

    // Other roles and the company see nothing here; nothing was changed.
    expect($engine->availableActions($application, staffWithRole('PAYMENT_OFFICER')))->toBe([])
        ->and($engine->availableActions($application, ownerOf($application)))->toBe([])
        ->and($application->fresh()->status)->toBe(ApplicationStatus::AdminReview);
});

it('offers the DG actions to an active delegate only', function () {
    $application = advanceTo(submittedApplication(), ApplicationStatus::DgDecision);
    $director = staffWithRole('DIRECTOR_GENERAL');
    $delegate = staffWithRole('REGISTRAR');
    $engine = app(WorkflowEngine::class);

    expect($engine->availableActions($application, $delegate))->toBe([]);

    Delegation::factory()->create(['delegator_id' => $director->id, 'delegate_id' => $delegate->id]);

    expect(collect($engine->availableActions($application, $delegate))->pluck('action')->all())
        ->toEqualCanonicalizing(['APPROVE', 'DEFER', 'REJECT']);
});

it('offers the company its submit action on a draft', function () {
    loadTestWorkflow();
    $application = Application::factory()->create();

    expect(app(WorkflowEngine::class)->availableActions($application, ownerOf($application)))->toBe([[
        'action' => 'SUBMIT',
        'to' => 'SUBMITTED',
        'requires_comment' => false,
        'requires_password' => true,
        'blocked_by' => [],
    ]]);
});
