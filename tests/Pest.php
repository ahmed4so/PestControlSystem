<?php

use App\Domain\Applications\Actions\RecordReviewItems;
use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\CompanyRegistry\Enums\DocumentType;
use App\Domain\Identity\Enums\StaffRole;
use App\Domain\Workflow\Definitions\DefinitionLoader;
use App\Domain\Workflow\Engine\WorkflowActor;
use App\Domain\Workflow\Engine\WorkflowEngine;
use App\Domain\Workflow\Enums\WorkflowActionCode;
use App\Models\Application;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Document;
use App\Models\Person;
use App\Models\Premises;
use App\Models\User;
use App\Models\WorkflowAction;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Spatie\Honeypot\EncryptedTime;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
| Helpers
*/

/**
 * A staff user ready to work: password set, 2FA confirmed (D-052).
 *
 * @param  list<string>  $roles
 * @param  array<string, mixed>  $attributes
 */
function staffUser(array $roles = [], array $attributes = []): User
{
    if ($roles !== []) {
        test()->seed(RolesAndPermissionsSeeder::class);
    }

    $user = User::factory()->withTwoFactor()->create($attributes);

    if ($roles !== []) {
        $user->syncRoles($roles);
    }

    return $user;
}

function systemAdministrator(): User
{
    return staffUser([StaffRole::SystemAdministrator->value]);
}

/**
 * A company account with its own company (Phase 2); pass false for an
 * account that has not set up its profile yet.
 *
 * @param  array<string, mixed>  $attributes
 */
function companyUser(array $attributes = [], bool $withCompany = true): CompanyUser
{
    $factory = CompanyUser::factory();

    return ($withCompany ? $factory->withCompany() : $factory)->create($attributes)->fresh();
}

/**
 * Valid honeypot fields: hidden field empty, form opened long enough ago
 * (D-080).
 *
 * @return array<string, string>
 */
function honeypotFields(int $openedSecondsAgo = 10): array
{
    return [
        config('honeypot.name_field_name').'_abc123' => '',
        config('honeypot.valid_from_field_name') => EncryptedTime::create(now()->subSeconds($openedSecondsAgo - (int) config('honeypot.amount_of_seconds')))->__toString(),
    ];
}

/**
 * Session values marking the password as just confirmed (D-085).
 *
 * @return array<string, int>
 */
function passwordConfirmed(): array
{
    return ['auth.password_confirmed_at' => time()];
}

/*
| Workflow helpers (Phase 3)
*/

/**
 * Loads the small engine test definition (tests/Fixtures/workflows).
 */
function loadTestWorkflow(): void
{
    app(DefinitionLoader::class)->load(require base_path('tests/Fixtures/workflows/test_flow_v1.php'));
}

function workflowActor(User|CompanyUser $user, bool $passwordConfirmed = true): WorkflowActor
{
    return new WorkflowActor($user, $passwordConfirmed ? time() : null, '127.0.0.1', 'Pest');
}

/**
 * The declaration a company types when it submits (02 §4.6).
 *
 * @return array<string, string>
 */
function declaration(): array
{
    return ['declaration_name' => 'Amina Hassan', 'declaration_designation' => 'Managing Director'];
}

/**
 * @param  array<string, mixed>  $payload
 */
function act(Application $application, WorkflowActionCode $action, User|CompanyUser $user, array $payload = [], bool $passwordConfirmed = true): WorkflowAction
{
    return app(WorkflowEngine::class)->perform($application, $action, workflowActor($user, $passwordConfirmed), $application->status, $payload);
}

/**
 * The company account that owns an application.
 */
function ownerOf(Application $application): CompanyUser
{
    $account = CompanyUser::query()->where('company_id', $application->company_id)->first()
        ?? CompanyUser::factory()->create(['company_id' => $application->company_id]);

    return $account->fresh();
}

/**
 * A draft submitted through the engine with the test definition.
 */
function submittedApplication(): Application
{
    loadTestWorkflow();
    $application = Application::factory()->create();
    act($application, WorkflowActionCode::Submit, ownerOf($application), declaration());

    return $application;
}

/**
 * A staff user holding one workflow role (by actor code, e.g. REGISTRAR).
 */
function staffWithRole(string $code): User
{
    return staffUser([StaffRole::fromCode($code)->value]);
}

/**
 * Moves an application from SUBMITTED to the given status with the test
 * definition (SUBMITTED, ADMIN_REVIEW, TECHNICAL_DOC_REVIEW, CONSOLIDATED_REVIEW,
 * DIRECTOR_REVIEW, PAYMENT_VERIFICATION, DG_DECISION).
 */
function advanceTo(Application $application, ApplicationStatus $target, ?User $registrar = null): Application
{
    $registrar ??= staffWithRole('REGISTRAR');
    $record = app(RecordReviewItems::class);

    $steps = [
        ApplicationStatus::Submitted->value => fn () => act($application, WorkflowActionCode::Acknowledge, $registrar),
        ApplicationStatus::AdminReview->value => function () use ($application, $registrar, $record) {
            $record->handle($application, $registrar, 'ADMIN_REVIEW', [
                'ITEM_A' => ['result' => 'COMPLIANT'], 'ITEM_B' => ['result' => 'COMPLIANT'],
            ]);
            act($application, WorkflowActionCode::CompleteAdminReview, $registrar);
        },
        ApplicationStatus::TechnicalDocReview->value => fn () => act($application, WorkflowActionCode::ScheduleInspection, $registrar),
        ApplicationStatus::ConsolidatedReview->value => fn () => act($application, WorkflowActionCode::CompleteConsolidatedReview, $registrar),
        ApplicationStatus::DirectorReview->value => fn () => act($application, WorkflowActionCode::Recommend, staffWithRole('DEPARTMENTAL_DIRECTOR')),
        ApplicationStatus::PaymentVerification->value => fn () => act($application, WorkflowActionCode::ClearPayment, staffWithRole('PAYMENT_OFFICER')),
    ];

    while ($application->status !== $target) {
        $step = $steps[$application->status->value] ?? throw new LogicException("advanceTo cannot leave {$application->status->value}.");
        $step();
    }

    return $application;
}
/**
 * A company whose profile is complete (D-178).
 */
function readyCompany(): Company
{
    $company = Company::factory()->withAddress()->create();
    $address = $company->only(['address_fms_id', 'address_region_id', 'address_town_or_market']);

    $owner = Person::factory()->create(['company_id' => $company->id, ...$address]);
    $company->forceFill(['owner_person_id' => $owner->id])->save();

    foreach ([DocumentType::CommerceRegistrationCertificate, DocumentType::TaxComplianceCertificate] as $type) {
        Document::factory()->certificate($type)->create(['company_id' => $company->id]);
    }

    foreach ([DocumentType::MdNationalId, DocumentType::CompanyProfile] as $type) {
        Document::factory()->create(['company_id' => $company->id, 'document_type' => $type]);
    }

    return $company;
}

/**
 * A technical person with qualification, degree certificate and CV.
 *
 * @param  array<string, mixed>  $attributes
 */
function technicalPerson(Company $company, array $attributes = []): Person
{
    $person = Person::factory()->create([
        'company_id' => $company->id,
        'qualification' => 'BSc Agriculture',
        ...$attributes,
    ]);

    foreach ([DocumentType::DegreeCertificate, DocumentType::Cv] as $type) {
        Document::factory()->create(['company_id' => $company->id, 'person_id' => $person->id, 'document_type' => $type]);
    }

    return $person;
}

/**
 * A draft of a ready company, with its technical person selected.
 *
 * @param  array<string, mixed>  $attributes
 */
function readyApplication(array $attributes = []): Application
{
    $company = readyCompany();
    $application = Application::factory()->create(['company_id' => $company->id, ...$attributes]);
    $application->technicalPersons()->attach(technicalPerson($company)->id);

    return $application;
}

/*
| Company License over HTTP (Phase 4A)
*/

/**
 * A ready draft with one premises selected, and its company account.
 *
 * @return array{0: Application, 1: CompanyUser}
 */
function licenseDraft(): array
{
    $application = readyApplication();
    $premises = Premises::factory()->create(['company_id' => $application->company_id]);
    $application->premises()->attach($premises->id);

    return [$application, ownerOf($application)];
}

function submitAs(CompanyUser $account, Application $application, array $overrides = []): TestResponse
{
    return test()->actingAs($account, 'company')->post(route('portal.applications.submit', $application), [
        'expected_status' => $application->fresh()->status->value,
        'declaration_name' => 'Amina Hassan',
        'declaration_designation' => 'Managing Director',
        'confirm' => '1',
        'password' => 'password',
        ...$overrides,
    ]);
}

function freshApplication(Application $application): Application
{
    return Application::query()->withoutGlobalScopes()->findOrFail($application->id);
}

function staffAction(User $user, Application $application, string $action, array $data = []): TestResponse
{
    return test()->actingAs($user, 'web')->post(route('admin.applications.actions', $application), [
        'action' => $action,
        'expected_status' => freshApplication($application)->status->value,
        ...$data,
    ]);
}
