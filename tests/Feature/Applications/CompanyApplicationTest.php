<?php

declare(strict_types=1);

use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Applications\Enums\LicenseCategory;
use App\Domain\Notifications\Mail\ApplicationAcknowledged;
use App\Domain\Notifications\Mail\ApplicationResubmitted;
use App\Domain\Notifications\Mail\ApplicationReturned;
use App\Domain\Notifications\Mail\ApplicationSubmitted;
use App\Domain\Notifications\Mail\ReviewerAssigned;
use App\Domain\Notifications\Mail\TechnicalReviewDone;
use App\Domain\Notifications\Mail\WorkItemReady;
use App\Domain\Workflow\Guards\PremisesSelected;
use App\Models\Application;
use App\Models\ApplicationVersion;
use App\Models\Company;
use App\Models\Premises;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * Company License from draft to technical review on the real definition
 * (09 Phase 4A rows A, D and H; T1–T6, T25), through both portals.
 */

beforeEach(function () {
    Mail::fake();
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->artisan('workflows:load')->assertSuccessful();
});

it('creates a draft that holds the category slot (D-044, D-193)', function () {
    $account = companyUser();

    $this->actingAs($account, 'company')
        ->post(route('portal.applications.store'), ['license_category' => 'IMPORTER'])
        ->assertRedirect();

    $draft = Application::query()->withoutGlobalScopes()->where('company_id', $account->company_id)->sole();
    expect($draft->status)->toBe(ApplicationStatus::Draft)
        ->and($draft->license_category)->toBe(LicenseCategory::Importer)
        ->and($draft->open_key)->toBe('COMPANY_LICENSE:IMPORTER');

    $this->actingAs($account, 'company')
        ->post(route('portal.applications.store'), ['license_category' => 'IMPORTER'])
        ->assertSessionHasErrors('license_category');
    $this->actingAs($account, 'company')
        ->post(route('portal.applications.store'), ['license_category' => 'RETAILER'])
        ->assertSessionHasNoErrors();
});

it('shows what still blocks the submission (D-191, D-197)', function () {
    $application = readyApplication();
    $account = ownerOf($application);

    $this->actingAs($account, 'company')
        ->get(route('portal.applications.show', $application))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('portal/applications/show')
            ->where('editable', true)
            ->where('submit.action', 'SUBMIT')
            ->where('submit.blocked_by', [PremisesSelected::REASON_NONE]));
});

it('selects only the company\'s own premises and technical person', function () {
    [$application, $account] = licenseDraft();
    $foreign = Premises::factory()->create();

    $this->actingAs($account, 'company')
        ->put(route('portal.applications.premises', $application), ['premises' => [$foreign->id]])
        ->assertSessionHasErrors('premises');

    $this->actingAs($account, 'company')
        ->put(route('portal.applications.technical-person', $application), ['person_id' => technicalPerson(Company::factory()->create())->id])
        ->assertSessionHasErrors('person_id');

    expect(freshApplication($application)->premises()->withoutGlobalScopes()->count())->toBe(1);
});

it('submits with the declaration: reference, version and emails (T1, 07 §3.3)', function () {
    [$application, $account] = licenseDraft();
    $registrar = staffWithRole('REGISTRAR');

    submitAs($account, $application)->assertSessionHasNoErrors()->assertRedirect();

    $submitted = freshApplication($application);
    expect($submitted->status)->toBe(ApplicationStatus::Submitted)
        ->and($submitted->reference)->toMatch('/^CL-\d{4}-\d{5}$/')
        ->and(ApplicationVersion::query()->where('application_id', $application->id)->sole()->declaration_designation)->toBe('Managing Director');

    Mail::assertQueued(ApplicationSubmitted::class, fn ($mail) => $mail->hasTo($account->email) && $mail->reference === $submitted->reference);
    Mail::assertQueued(WorkItemReady::class, fn ($mail) => $mail->hasTo($registrar->email));
});

it('refuses to submit without the password, the confirmation or a complete file', function () {
    [$application, $account] = licenseDraft();

    submitAs($account, $application, ['password' => 'wrong-password'])->assertSessionHasErrors('password');
    submitAs($account, $application, ['confirm' => ''])->assertSessionHasErrors('confirm');

    $application->premises()->detach();
    submitAs($account, $application)->assertSessionHasErrors(['submit' => PremisesSelected::REASON_NONE]);

    expect(freshApplication($application)->status)->toBe(ApplicationStatus::Draft);
    Mail::assertNothingQueued();
});

it('gives 404 for another company\'s application and keeps staff out of the Company Portal', function () {
    [$application] = licenseDraft();
    $other = companyUser();

    $this->actingAs($other, 'company')->get(route('portal.applications.show', $application))->assertNotFound();
    submitAs($other, $application)->assertNotFound();

    // A staff session is not a company session: the company scope finds nothing.
    $this->actingAs(staffWithRole('REGISTRAR'), 'web')->get(route('portal.applications.show', $application))->assertNotFound();
    expect(freshApplication($application)->status)->toBe(ApplicationStatus::Draft);
});

it('locks the choices once submitted (D-073)', function () {
    [$application, $account] = licenseDraft();
    submitAs($account, $application);

    $this->actingAs($account, 'company')
        ->put(route('portal.applications.premises', $application), ['premises' => []])
        ->assertForbidden();
});

it('opens the application file for staff with masked national IDs, and keeps companies out', function () {
    [$application, $account] = licenseDraft();
    submitAs($account, $application);
    $person = $application->technicalPersons()->withoutGlobalScopes()->sole();

    // A company session never reaches the Admin Portal (D-048).
    $this->actingAs($account, 'company')->get(route('admin.applications.show', $application))->assertRedirect();

    $this->actingAs(staffWithRole('REGISTRAR'), 'web')
        ->get(route('admin.applications.show', $application))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/applications/show')
            ->where('application.status', 'SUBMITTED')
            ->where('actions.0.action', 'ACKNOWLEDGE')
            ->where('snapshot.technical_persons.0.national_id', fn ($masked) => $masked !== $person->national_id_no)
            ->missing('snapshot.technical_persons.0.national_id_encrypted'));
    $this->actingAs(staffWithRole('TECHNICAL_REVIEWER'), 'web')->get(route('admin.applications.show', $application))->assertForbidden();
});

it('acknowledges, reviews, returns and resubmits with the right emails (T2–T4, T25)', function () {
    [$application, $account] = licenseDraft();
    $registrar = staffWithRole('REGISTRAR');
    $colleague = staffWithRole('REGISTRAR');
    submitAs($account, $application);

    staffAction($registrar, $application, 'ACKNOWLEDGE')->assertSessionHasNoErrors();
    Mail::assertQueued(ApplicationAcknowledged::class, fn ($mail) => $mail->hasTo($account->email));
    Mail::assertQueued(WorkItemReady::class, fn ($mail) => $mail->hasTo($colleague->email));
    Mail::assertNotQueued(WorkItemReady::class, fn ($mail) => $mail->hasTo($registrar->email) && $mail->data['stage_en'] === 'Administrative review');

    $this->actingAs($registrar, 'web')->post(route('admin.applications.review-items', $application), [
        'stage' => 'ADMIN_REVIEW',
        'items' => [
            'COMMERCE_REGISTRATION' => ['result' => 'DEFICIENT', 'comment' => 'The certificate is not readable.'],
            'TAX_COMPLIANCE' => ['result' => 'COMPLIANT'],
            'IDENTIFICATION' => ['result' => ''],
        ],
    ])->assertSessionHasNoErrors();

    staffAction($registrar, $application, 'RETURN_TO_COMPANY')->assertSessionHasErrors('action');
    staffAction($registrar, $application, 'RETURN_TO_COMPANY', ['comment' => 'Please upload a readable copy.'])->assertSessionHasNoErrors();

    expect(freshApplication($application)->status)->toBe(ApplicationStatus::Returned);
    Mail::assertQueued(ApplicationReturned::class, fn ($mail) => $mail->hasTo($account->email)
        && $mail->data['comment'] === 'Please upload a readable copy.'
        && $mail->data['items'][0]['label'] === 'Commerce registration');

    $this->actingAs($account, 'company')
        ->get(route('portal.applications.show', $application))
        ->assertInertia(fn (Assert $page) => $page
            ->where('returned.comment', 'Please upload a readable copy.')
            ->where('returned.items.0.comment', 'The certificate is not readable.')
            ->where('submit.action', 'RESUBMIT'));

    submitAs($account, $application, ['response_note' => 'Uploaded a clear copy.'])->assertSessionHasNoErrors();

    $resubmitted = freshApplication($application);
    expect($resubmitted->status)->toBe(ApplicationStatus::AdminReview)
        ->and($resubmitted->current_version_no)->toBe(2);
    Mail::assertQueued(ApplicationResubmitted::class, fn ($mail) => $mail->hasTo($registrar->email) && $mail->data['version'] === '2');
});

it('emails the assigned Technical Reviewer and, when done, the Registrar who assigned (T5, T6)', function () {
    [$application, $account] = licenseDraft();
    $registrar = staffWithRole('REGISTRAR');
    $reviewer = staffWithRole('TECHNICAL_REVIEWER');
    submitAs($account, $application);
    staffAction($registrar, $application, 'ACKNOWLEDGE');

    $items = collect(['COMMERCE_REGISTRATION', 'TAX_COMPLIANCE', 'IDENTIFICATION', 'COMPANY_PROFILE', 'CATEGORY', 'APPLICATION_COMPLETENESS', 'SUPPORTING_DOCUMENTS'])
        ->mapWithKeys(fn ($code) => [$code => ['result' => 'COMPLIANT']])->all();
    $this->actingAs($registrar, 'web')->post(route('admin.applications.review-items', $application), ['stage' => 'ADMIN_REVIEW', 'items' => $items]);
    staffAction($registrar, $application, 'COMPLETE_ADMIN_REVIEW')->assertSessionHasNoErrors();

    staffAction($registrar, $application, 'ASSIGN_TECHNICAL_REVIEWER', ['assignee_id' => $reviewer->id])->assertSessionHasNoErrors();
    Mail::assertQueued(ReviewerAssigned::class, fn ($mail) => $mail->hasTo($reviewer->email));

    $this->actingAs($reviewer, 'web')->get(route('admin.applications.show', $application))->assertOk();
    $this->actingAs($reviewer, 'web')->post(route('admin.applications.review-items', $application), [
        'stage' => 'TECHNICAL_DOC_REVIEW',
        'items' => ['TECHNICAL_QUALIFICATION' => ['result' => 'COMPLIANT'], 'TECHNICAL_CV' => ['result' => 'COMPLIANT']],
    ])->assertSessionHasNoErrors();
    staffAction($reviewer, $application, 'COMPLETE_TECHNICAL_REVIEW')->assertSessionHasNoErrors();

    Mail::assertQueued(TechnicalReviewDone::class, fn ($mail) => $mail->hasTo($registrar->email));
    expect(freshApplication($application)->status)->toBe(ApplicationStatus::TechnicalDocReview);
});

it('sends nothing when an action is refused', function () {
    [$application, $account] = licenseDraft();
    submitAs($account, $application);
    Mail::fake();

    staffAction(staffWithRole('PAYMENT_OFFICER'), $application, 'ACKNOWLEDGE')->assertSessionHasErrors('action');

    Mail::assertNothingQueued();
    expect(freshApplication($application)->status)->toBe(ApplicationStatus::Submitted);
});
