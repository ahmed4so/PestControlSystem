<?php

declare(strict_types=1);

use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Applications\Enums\ApplicationType;
use App\Domain\Applications\Enums\LicenseCategory;
use App\Domain\Applications\Enums\Service;
use App\Domain\CompanyRegistry\Enums\DocumentType;
use App\Domain\Workflow\Engine\TransitionContext;
use App\Domain\Workflow\Enums\WorkflowActionCode;
use App\Domain\Workflow\Exceptions\TransitionRefused;
use App\Domain\Workflow\Guards\CertificatesValid;
use App\Domain\Workflow\Guards\NoOpenAssignment;
use App\Domain\Workflow\Guards\NoOtherOpenApplication;
use App\Domain\Workflow\Guards\NotYetAvailableGuard;
use App\Domain\Workflow\Guards\ProfileComplete;
use App\Domain\Workflow\Guards\RequiredDocuments;
use App\Domain\Workflow\Guards\TechnicalPersonSelected;
use App\Domain\Workflow\Registry\WorkflowRegistry;
use App\Models\Application;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Document;
use App\Models\User;
use App\Models\WorkflowTemplate;
use Illuminate\Database\UniqueConstraintViolationException;

/*
 * Guards built in Phase 3 (04 §11): each passes its good case and blocks
 * its failure case with its message. Guards and effects of later phases
 * refuse or throw (D-188).
 */

/**
 * The context of the test definition's SUBMIT, for checking one guard.
 */
function submitContext(Application $application, User|CompanyUser|null $user = null, array $payload = []): TransitionContext
{
    loadTestWorkflow();
    $template = WorkflowTemplate::query()->where('code', 'TEST_FLOW_V1')->firstOrFail()->load(['stages', 'transitions']);
    $transition = $template->transitions->firstOrFail(fn ($transition) => $transition->action === WorkflowActionCode::Submit);

    return new TransitionContext($application, $template, $transition, workflowActor($user ?? ownerOf($application)), $payload, null);
}

function reasonsOf(string $guardClass, Application $application): array
{
    return app($guardClass)->check(submitContext($application), null)->reasons;
}

it('passes every Phase 3 submit guard for a ready application', function (string $guard) {
    expect(reasonsOf($guard, readyApplication()))->toBe([]);
})->with([
    ProfileComplete::class,
    CertificatesValid::class,
    RequiredDocuments::class,
    TechnicalPersonSelected::class,
    NoOtherOpenApplication::class,
]);

it('blocks an incomplete profile and says what is missing', function () {
    $application = readyApplication();
    Company::query()->whereKey($application->company_id)->update(['owner_person_id' => null]);

    expect(reasonsOf(ProfileComplete::class, $application))
        ->toBe([ProfileComplete::REASON, 'Owner / Managing Director']);
});

it('blocks a missing or expired certificate', function () {
    $application = readyApplication();
    Document::query()->withoutGlobalScopes()
        ->where('company_id', $application->company_id)
        ->where('document_type', DocumentType::TaxComplianceCertificate)
        ->update(['expires_on' => now()->subDay()->toDateString()]);
    Document::query()->withoutGlobalScopes()
        ->where('company_id', $application->company_id)
        ->where('document_type', DocumentType::CommerceRegistrationCertificate)
        ->delete();

    expect(reasonsOf(CertificatesValid::class, $application))->toBe([
        __(CertificatesValid::REASON_MISSING, ['certificate' => __(DocumentType::CommerceRegistrationCertificate->label())]),
        __(CertificatesValid::REASON_EXPIRED, ['certificate' => __(DocumentType::TaxComplianceCertificate->label())]),
    ]);
});

it('blocks a missing required document, and asks Operator License applicants for the equipment inventory', function () {
    $application = readyApplication();
    Document::query()->withoutGlobalScopes()
        ->where('company_id', $application->company_id)
        ->where('document_type', DocumentType::CompanyProfile)
        ->delete();

    expect(reasonsOf(RequiredDocuments::class, $application))
        ->toBe([__(RequiredDocuments::REASON, ['document' => __(DocumentType::CompanyProfile->label())])]);

    $operator = readyApplication(['service' => Service::OperatorLicense, 'license_category' => null, 'open_key' => 'OPERATOR_LICENSE']);

    expect(reasonsOf(RequiredDocuments::class, $operator))
        ->toBe([__(RequiredDocuments::REASON, ['document' => __(DocumentType::EquipmentInventory->label())])]);
});

it('needs exactly one ready technical person of the company', function () {
    $application = readyApplication();
    $company = Company::query()->findOrFail($application->company_id);

    $application->technicalPersons()->attach(technicalPerson($company)->id);
    expect(reasonsOf(TechnicalPersonSelected::class, $application))->toBe([TechnicalPersonSelected::REASON_ONE]);

    $application->technicalPersons()->detach();
    expect(reasonsOf(TechnicalPersonSelected::class, $application))->toBe([TechnicalPersonSelected::REASON_ONE]);

    $other = technicalPerson(Company::factory()->create());
    $application->technicalPersons()->attach($other->id);
    expect(reasonsOf(TechnicalPersonSelected::class, $application))->toBe([TechnicalPersonSelected::REASON_ONE]);

    $application->technicalPersons()->sync([technicalPerson($company, ['released_at' => now()])->id]);
    expect(reasonsOf(TechnicalPersonSelected::class, $application))->toBe([TechnicalPersonSelected::REASON_ONE]);
});

it('needs the technical person\'s qualification, degree and CV', function () {
    $application = readyApplication();
    $person = $application->technicalPersons()->withoutGlobalScopes()->sole();
    $person->forceFill(['qualification' => null])->save();
    Document::query()->withoutGlobalScopes()->where('person_id', $person->id)->where('document_type', DocumentType::Cv)->delete();

    expect(reasonsOf(TechnicalPersonSelected::class, $application))->toBe([
        TechnicalPersonSelected::REASON_QUALIFICATION,
        __(TechnicalPersonSelected::REASON_DOCUMENT, ['document' => __(DocumentType::Cv->label())]),
    ]);
});

it('blocks a second open application of the same service and category (D-044)', function () {
    $application = readyApplication();
    $other = Application::factory()->category(LicenseCategory::Retailer)->create(['company_id' => $application->company_id]);
    $other->forceFill(['open_key' => $application->open_key, 'license_category' => LicenseCategory::Importer]);

    // The database key already refuses a second row with the same key ...
    expect(fn () => $other->save())->toThrow(UniqueConstraintViolationException::class);

    // ... and the guard reports it when the key is held by another row.
    $application->forceFill(['open_key' => null])->save();
    $other->refresh()->forceFill(['open_key' => Service::CompanyLicense->openKey(LicenseCategory::Importer)])->save();

    expect(reasonsOf(NoOtherOpenApplication::class, $application))->toBe([NoOtherOpenApplication::REASON]);
});

it('never blocks Import Permits on open applications (D-072)', function () {
    $permit = readyApplication(['service' => Service::ImportPermit, 'license_category' => null, 'open_key' => null]);
    Application::factory()->create([
        'company_id' => $permit->company_id, 'service' => Service::ImportPermit, 'license_category' => null, 'open_key' => null,
    ]);

    expect(reasonsOf(NoOtherOpenApplication::class, $permit))->toBe([]);
});

it('waits for the assigned Technical Reviewer before scheduling the inspection', function () {
    $registrar = staffWithRole('REGISTRAR');
    $application = advanceTo(submittedApplication(), ApplicationStatus::TechnicalDocReview, $registrar);
    act($application, WorkflowActionCode::AssignTechnicalReviewer, $registrar, ['assignee_id' => staffWithRole('TECHNICAL_REVIEWER')->id]);

    try {
        act($application, WorkflowActionCode::ScheduleInspection, $registrar);
        $this->fail('Expected a refusal.');
    } catch (TransitionRefused $refused) {
        expect($refused->reasons)->toBe([NoOpenAssignment::REASON]);
    }
});

it('refuses every guard of a later phase (D-188)', function (string $name) {
    [$guard] = app(WorkflowRegistry::class)->guard($name);

    expect($guard)->toBeInstanceOf(NotYetAvailableGuard::class)
        ->and($guard->check(submitContext(readyApplication(['application_type' => ApplicationType::Renewal])), null)->reasons)
        ->toBe([NotYetAvailableGuard::REASON]);
})->with([
    'FeeScheduleExists',
    'AmountMatchesLetter', 'ReceiptNotUsed', 'PaymentCleared', 'SignerHasSignature', 'PreviousLicenseActive',
    'RenewalWindowOpen', 'ImportationLicenseActive', 'ProductVerified', 'IngredientNotBanned', 'StoragePremisesSelected',
]);

it('lets renewal-only guards pass for a new application (04 §8)', function (string $name) {
    [$guard] = app(WorkflowRegistry::class)->guard($name);

    expect($guard->check(submitContext(readyApplication()), null)->passed)->toBeTrue();
})->with(['PreviousLicenseActive', 'RenewalWindowOpen']);

it('throws from every effect of a later phase, so the transition rolls back (D-188)', function (string $name) {
    [$effect] = app(WorkflowRegistry::class)->effect($name);
    $context = submitContext(readyApplication(['application_type' => ApplicationType::Amendment]));

    expect(fn () => $effect->apply($context, null))->toThrow(LogicException::class, "[{$name}]");
})->with([
    'IssuePermissionToPayLetter', 'RecordPayment', 'ClearPayment', 'RejectPayment',
    'IssueLicense', 'SupersedePreviousLicense', 'ClearAmendmentFlag', 'FlagRestrictedIngredient',
]);

it('refuses an email whose recipients come in a later phase, and an unknown code (D-188)', function (string $code) {
    [$effect] = app(WorkflowRegistry::class)->effect("Notify:{$code}");

    expect(fn () => $effect->apply(submitContext(readyApplication()), $code))->toThrow(LogicException::class);
})->with(['PAYMENT_REQUIRED', 'LICENSE_ISSUED', 'NO_SUCH_EMAIL']);
