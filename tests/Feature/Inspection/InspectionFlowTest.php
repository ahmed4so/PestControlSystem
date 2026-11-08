<?php

declare(strict_types=1);

use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Applications\Enums\Service;
use App\Domain\Inspection\Enums\InspectionStatus;
use App\Domain\Inspection\Support\InspectionChecklist;
use App\Domain\Inspection\Support\InspectionGuards;
use App\Domain\Notifications\Mail\InspectionRescheduled;
use App\Domain\Notifications\Mail\InspectionScheduled;
use App\Domain\Notifications\Mail\WorkItemReady;
use App\Domain\Workflow\Checks\AssignmentCheck;
use App\Models\Application;
use App\Models\AuditActivity;
use App\Models\CompanyUser;
use App\Models\Inspection;
use App\Models\InspectionMedia;
use App\Models\Premises;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * Inspection (09 Phase 4A rows F and G; T7–T14, D-077, D-136, D-196) on the
 * real Company License definition.
 */

beforeEach(function () {
    Mail::fake();
    Storage::fake((string) config('documents.disk'));
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->artisan('workflows:load')->assertSuccessful();
    $this->registrar = staffWithRole('REGISTRAR');
    $this->inspectorA = staffWithRole('INSPECTOR');
    $this->inspectorB = staffWithRole('INSPECTOR');
});

/**
 * An application with two premises, through admin and technical review.
 *
 * @return array{0: Application, 1: CompanyUser}
 */
function atTechnicalReview(): array
{
    [$application, $account] = licenseDraft();
    $application->premises()->attach(Premises::factory()->create(['company_id' => $application->company_id])->id);
    submitAs($account, $application)->assertSessionHasNoErrors();
    $registrar = test()->registrar;

    staffAction($registrar, $application, 'ACKNOWLEDGE')->assertSessionHasNoErrors();
    $admin = collect(['COMMERCE_REGISTRATION', 'TAX_COMPLIANCE', 'IDENTIFICATION', 'COMPANY_PROFILE', 'CATEGORY', 'APPLICATION_COMPLETENESS', 'SUPPORTING_DOCUMENTS'])
        ->mapWithKeys(fn ($code) => [$code => ['result' => 'COMPLIANT']])->all();
    test()->actingAs($registrar, 'web')->post(route('admin.applications.review-items', $application), ['stage' => 'ADMIN_REVIEW', 'items' => $admin]);
    staffAction($registrar, $application, 'COMPLETE_ADMIN_REVIEW')->assertSessionHasNoErrors();
    test()->actingAs($registrar, 'web')->post(route('admin.applications.review-items', $application), [
        'stage' => 'TECHNICAL_DOC_REVIEW',
        'items' => ['TECHNICAL_QUALIFICATION' => ['result' => 'COMPLIANT'], 'TECHNICAL_CV' => ['result' => 'COMPLIANT']],
    ])->assertSessionHasNoErrors();

    return [$application, $account];
}

/**
 * @return list<array{premises_id: int, inspector_id: int, planned_date: string}>
 */
function inspectionPlan(Application $application, ?string $date = null): array
{
    $premises = freshApplication($application)->currentVersion()->snapshot['premises'];
    $inspectors = [test()->inspectorA, test()->inspectorB];

    return array_values(array_map(fn (array $item, int $index) => [
        'premises_id' => $item['id'],
        'inspector_id' => $inspectors[$index % 2]->id,
        'planned_date' => $date ?? now()->addDays(3)->toDateString(),
    ], $premises, array_keys($premises)));
}

/**
 * @return array{0: Application, 1: CompanyUser}
 */
function scheduled(): array
{
    [$application, $account] = atTechnicalReview();
    staffAction(test()->registrar, $application, 'SCHEDULE_INSPECTION', ['inspections' => inspectionPlan($application)])->assertSessionHasNoErrors();

    return [$application, $account];
}

function inspectionOf(User $inspector, Application $application): Inspection
{
    return Inspection::query()->where('application_id', $application->id)->where('inspector_id', $inspector->id)->latest('round')->firstOrFail();
}

/**
 * A complete report: every item compliant, facts, result and GPS.
 */
function completeReport(User $inspector, Inspection $inspection): void
{
    $items = collect(InspectionChecklist::items(Service::CompanyLicense))
        ->map(fn () => ['result' => 'COMPLIANT', 'note' => null])->all();

    test()->actingAs($inspector, 'web')->put(route('admin.inspections.report', $inspection), [
        'items' => $items,
        'observed_facts' => 'Clean, ventilated warehouse; products stored on pallets.',
        'overall_result' => 'SATISFACTORY',
        'gps_lat' => '2.0469',
        'gps_lng' => '45.3182',
    ])->assertSessionHasNoErrors();
}

function signAs(User $inspector, Inspection $inspection, string $password = 'password'): TestResponse
{
    return test()->actingAs($inspector, 'web')->post(route('admin.inspections.sign', $inspection), ['password' => $password]);
}

it('needs a complete plan to schedule the inspection (T7)', function () {
    [$application] = atTechnicalReview();
    $plan = inspectionPlan($application);

    staffAction($this->registrar, $application, 'SCHEDULE_INSPECTION', ['inspections' => [$plan[0]]])
        ->assertSessionHasErrors(['action' => InspectionGuards::REASON_PLAN]);

    $notInspector = $plan;
    $notInspector[1]['inspector_id'] = staffWithRole('PAYMENT_OFFICER')->id;
    staffAction($this->registrar, $application, 'SCHEDULE_INSPECTION', ['inspections' => $notInspector])
        ->assertSessionHasErrors(['action' => InspectionGuards::REASON_INSPECTOR]);

    staffAction($this->registrar, $application, 'SCHEDULE_INSPECTION', ['inspections' => inspectionPlan($application, now()->subDay()->toDateString())])
        ->assertSessionHasErrors(['action' => InspectionGuards::REASON_DATE]);

    expect(Inspection::query()->count())->toBe(0)
        ->and(freshApplication($application)->status)->toBe(ApplicationStatus::TechnicalDocReview);
});

it('schedules one inspection per premises and tells the company and each Inspector (T7)', function () {
    [$application, $account] = scheduled();
    $bystander = staffWithRole('INSPECTOR');

    expect(freshApplication($application)->status)->toBe(ApplicationStatus::InspectionScheduled)
        ->and(Inspection::query()->where('application_id', $application->id)->where('round', 1)->count())->toBe(2);

    Mail::assertQueued(InspectionScheduled::class, fn ($mail) => $mail->hasTo($account->email));
    Mail::assertQueued(InspectionScheduled::class, fn ($mail) => $mail->hasTo($this->inspectorA->email));
    Mail::assertQueued(InspectionScheduled::class, fn ($mail) => $mail->hasTo($this->inspectorB->email));
    Mail::assertNotQueued(InspectionScheduled::class, fn ($mail) => $mail->hasTo($bystander->email));
    Mail::assertNotQueued(WorkItemReady::class, fn ($mail) => $mail->hasTo($bystander->email));

    $this->actingAs($account, 'company')->get(route('portal.applications.show', $application))
        ->assertInertia(fn (Assert $page) => $page->count('inspections', 2)->missing('inspections.0.observed_facts'));
});

it('lets the Registrar move an inspection not yet visited, with a reason (T9)', function () {
    [$application, $account] = scheduled();
    $inspection = inspectionOf($this->inspectorA, $application);
    $date = now()->addDays(10)->toDateString();

    $this->actingAs($this->registrar, 'web')->post(route('admin.inspections.reschedule', $inspection), ['planned_date' => $date])
        ->assertSessionHasErrors('comment');
    $this->actingAs($this->inspectorA, 'web')->post(route('admin.inspections.reschedule', $inspection), ['planned_date' => $date, 'comment' => 'Road closed.'])
        ->assertSessionHasErrors('inspection');

    $this->actingAs($this->registrar, 'web')->post(route('admin.inspections.reschedule', $inspection), ['planned_date' => $date, 'comment' => 'Road closed.'])
        ->assertSessionHasNoErrors();

    expect($inspection->fresh()->planned_date->toDateString())->toBe($date)
        ->and(freshApplication($application)->status)->toBe(ApplicationStatus::InspectionScheduled);
    Mail::assertQueued(InspectionRescheduled::class, fn ($mail) => $mail->hasTo($account->email) && $mail->data['date'] === $date);
    Mail::assertQueued(InspectionRescheduled::class, fn ($mail) => $mail->hasTo($this->inspectorA->email));
});

it('lets only the assigned Inspector record the visit (T10)', function () {
    [$application] = scheduled();
    $inspection = inspectionOf($this->inspectorA, $application);

    $this->actingAs($this->inspectorB, 'web')->post(route('admin.inspections.visit', $inspection))
        ->assertSessionHasErrors(['inspection' => AssignmentCheck::REASON]);
    $this->actingAs(staffWithRole('INSPECTOR'), 'web')->get(route('admin.inspections.show', $inspection))->assertForbidden();

    $this->actingAs($this->inspectorA, 'web')->post(route('admin.inspections.visit', $inspection))->assertSessionHasNoErrors();

    expect($inspection->fresh()->conducted_at)->not->toBeNull()
        ->and(freshApplication($application)->status)->toBe(ApplicationStatus::InspectionReport);
});

it('refuses a report before the visit, and an incomplete report at signing (T11)', function () {
    [$application] = scheduled();
    $inspection = inspectionOf($this->inspectorA, $application);

    $this->actingAs($this->inspectorA, 'web')->put(route('admin.inspections.report', $inspection), ['observed_facts' => 'Too early.'])
        ->assertSessionHasErrors('inspection');

    $this->actingAs($this->inspectorA, 'web')->post(route('admin.inspections.visit', $inspection));
    $this->actingAs($this->inspectorA, 'web')->put(route('admin.inspections.report', $inspection), [
        'items' => ['FIRE_SAFETY' => ['result' => 'NON_COMPLIANT', 'note' => 'No extinguisher.']],
    ])->assertSessionHasNoErrors();

    signAs($this->inspectorA, $inspection)->assertSessionHasErrors('inspection');
    $errors = session('errors')->get('inspection')[0];
    expect($errors)->toContain(InspectionGuards::REASON_ITEMS)
        ->toContain(InspectionGuards::REASON_FACTS)
        ->toContain(InspectionGuards::REASON_DEFICIENCIES)
        ->toContain(InspectionGuards::REASON_PHOTOS)
        ->toContain(InspectionGuards::REASON_GPS);

    signAs($this->inspectorA, $inspection, 'wrong-password')->assertSessionHasErrors('password');
    expect($inspection->fresh()->signed_at)->toBeNull();
});

it('accepts a missing GPS position with a reason (D-136)', function () {
    [$application] = scheduled();
    $inspection = inspectionOf($this->inspectorA, $application);
    $this->actingAs($this->inspectorA, 'web')->post(route('admin.inspections.visit', $inspection));
    completeReport($this->inspectorA, $inspection);

    $this->actingAs($this->inspectorA, 'web')->put(route('admin.inspections.report', $inspection), [
        'gps_lat' => null, 'gps_lng' => null, 'gps_unavailable_reason' => 'No signal inside the warehouse.',
    ])->assertSessionHasNoErrors();

    signAs($this->inspectorA, $inspection)->assertSessionHasNoErrors();
    expect($inspection->fresh()->signed_at)->not->toBeNull();
});

it('refuses a position outside Somalia', function () {
    [$application] = scheduled();
    $inspection = inspectionOf($this->inspectorA, $application);
    $this->actingAs($this->inspectorA, 'web')->post(route('admin.inspections.visit', $inspection));

    $this->actingAs($this->inspectorA, 'web')->put(route('admin.inspections.report', $inspection), ['gps_lat' => '48.85', 'gps_lng' => '2.35'])
        ->assertSessionHasErrors(['gps_lat', 'gps_lng']);
});

it('moves on to consolidated review only when the last report of the round is signed (T11, D-196)', function () {
    [$application] = scheduled();
    $first = inspectionOf($this->inspectorA, $application);
    $second = inspectionOf($this->inspectorB, $application);

    $this->actingAs($this->inspectorA, 'web')->post(route('admin.inspections.visit', $first));
    // The application is already at INSPECTION_REPORT: the second visit is an Inspection action.
    $this->actingAs($this->inspectorB, 'web')->post(route('admin.inspections.visit', $second))->assertSessionHasNoErrors();
    expect($second->fresh()->conducted_at)->not->toBeNull();

    completeReport($this->inspectorA, $first);
    signAs($this->inspectorA, $first)->assertSessionHasNoErrors();
    expect(freshApplication($application)->status)->toBe(ApplicationStatus::InspectionReport)
        ->and($first->fresh()->status)->toBe(InspectionStatus::Completed);

    completeReport($this->inspectorB, $second);
    signAs($this->inspectorB, $second)->assertSessionHasNoErrors();

    expect(freshApplication($application)->status)->toBe(ApplicationStatus::ConsolidatedReview);
    Mail::assertQueued(WorkItemReady::class, fn ($mail) => $mail->hasTo($this->registrar->email) && $mail->data['stage_en'] === 'Consolidated review');
});

it('locks a signed report (procedure 6.7)', function () {
    [$application] = scheduled();
    $inspection = inspectionOf($this->inspectorA, $application);
    $this->actingAs($this->inspectorA, 'web')->post(route('admin.inspections.visit', $inspection));
    completeReport($this->inspectorA, $inspection);
    signAs($this->inspectorA, $inspection);

    $this->actingAs($this->inspectorA, 'web')->put(route('admin.inspections.report', $inspection), ['observed_facts' => 'Changed.'])
        ->assertSessionHasErrors('inspection');
    expect(fn () => $inspection->fresh()->forceFill(['observed_facts' => 'Changed.'])->save())->toThrow(LogicException::class)
        ->and($inspection->fresh()->observed_facts)->not->toBe('Changed.');
});

it('stores photos and an MP4 video, and refuses other files (D-135, D-200)', function () {
    [$application] = scheduled();
    $inspection = inspectionOf($this->inspectorA, $application);
    $this->actingAs($this->inspectorA, 'web')->post(route('admin.inspections.visit', $inspection));
    $this->actingAs($this->inspectorA, 'web')->put(route('admin.inspections.report', $inspection), [
        'items' => ['FIRE_SAFETY' => ['result' => 'NON_COMPLIANT', 'note' => 'No extinguisher.']],
    ]);

    $photo = UploadedFile::fake()->image('fire.jpg', 800, 600);
    $this->actingAs($this->inspectorA, 'web')->post(route('admin.inspections.media', $inspection), ['file' => $photo, 'type' => 'PHOTO', 'item_code' => 'FIRE_SAFETY'])
        ->assertSessionHasNoErrors();

    $mp4 = UploadedFile::fake()->createWithContent('walk.mp4', "\x00\x00\x00\x18ftypmp42\x00\x00\x00\x00mp42isom".str_repeat("\x00", 64));
    $this->actingAs($this->inspectorA, 'web')->post(route('admin.inspections.media', $inspection), ['file' => $mp4, 'type' => 'VIDEO'])
        ->assertSessionHasNoErrors();

    $fake = UploadedFile::fake()->createWithContent('walk.mp4', '%PDF-1.4 not a video');
    $this->actingAs($this->inspectorA, 'web')->post(route('admin.inspections.media', $inspection), ['file' => $fake, 'type' => 'VIDEO'])
        ->assertSessionHasErrors(['file' => 'Upload an MP4 video.']);

    expect(InspectionMedia::query()->where('inspection_id', $inspection->id)->pluck('media_type')->map->value->sort()->values()->all())->toBe(['PHOTO', 'VIDEO']);

    $media = InspectionMedia::query()->where('media_type', 'PHOTO')->sole();
    // A signed 5-minute URL (redirect) or the file itself, depending on the disk.
    $status = $this->actingAs($this->registrar, 'web')->get(route('admin.inspections.file', [$inspection, $media]))->status();
    expect($status)->toBeIn([200, 302]);
    expect(AuditActivity::query()->where('event', 'inspection_media_viewed')->exists())->toBeTrue();
});

it('shows the Inspector their open inspections in "My work"', function () {
    [$application] = scheduled();

    $this->actingAs($this->inspectorA, 'web')->get(route('admin.queues'))
        ->assertInertia(fn (Assert $page) => $page->where('items.data.0.id', $application->id));
    $this->actingAs(staffWithRole('INSPECTOR'), 'web')->get(route('admin.queues'))
        ->assertInertia(fn (Assert $page) => $page->count('items.data', 0));
});

/**
 * An application at CONSOLIDATED_REVIEW, both reports signed.
 */
function atConsolidatedReview(): array
{
    [$application, $account] = scheduled();

    foreach ([test()->inspectorA, test()->inspectorB] as $inspector) {
        $inspection = inspectionOf($inspector, $application);
        test()->actingAs($inspector, 'web')->post(route('admin.inspections.visit', $inspection));
        completeReport($inspector, $inspection);
        signAs($inspector, $inspection)->assertSessionHasNoErrors();
    }

    expect(freshApplication($application)->status)->toBe(ApplicationStatus::ConsolidatedReview);

    return [$application, $account];
}

function consolidatedItems(string $first = 'COMPLIANT', ?string $comment = null): array
{
    return [
        'DOCUMENTS_CONSISTENT' => ['result' => $first, 'comment' => $comment],
        'INSPECTION_REPORT_PRESENT' => ['result' => 'COMPLIANT'],
        'INSPECTION_FINDINGS_ADDRESSED' => ['result' => 'COMPLIANT'],
        'OVERALL_COMPLIANCE' => ['result' => 'COMPLIANT'],
    ];
}

it('completes the consolidated review and goes to the Director (T12)', function () {
    [$application] = atConsolidatedReview();
    $director = staffWithRole('DEPARTMENTAL_DIRECTOR');

    staffAction($this->registrar, $application, 'COMPLETE_CONSOLIDATED_REVIEW')->assertSessionHasErrors('action');

    $this->actingAs($this->registrar, 'web')->post(route('admin.applications.review-items', $application), ['stage' => 'CONSOLIDATED_REVIEW', 'items' => consolidatedItems()]);
    staffAction($this->registrar, $application, 'COMPLETE_CONSOLIDATED_REVIEW')->assertSessionHasNoErrors();

    // No payment exists yet, so the conditional target goes to the Director (D-043).
    expect(freshApplication($application)->status)->toBe(ApplicationStatus::DirectorReview);
    Mail::assertQueued(WorkItemReady::class, fn ($mail) => $mail->hasTo($director->email));
});

it('returns to the company from the consolidated review and continues there on resubmission (T13, T25)', function () {
    [$application, $account] = atConsolidatedReview();

    $this->actingAs($this->registrar, 'web')->post(route('admin.applications.review-items', $application), [
        'stage' => 'CONSOLIDATED_REVIEW', 'items' => consolidatedItems('DEFICIENT', 'The premises address differs from the certificate.'),
    ]);
    staffAction($this->registrar, $application, 'RETURN_TO_COMPANY', ['comment' => 'Correct the premises address.'])->assertSessionHasNoErrors();
    expect(freshApplication($application)->resume_stage)->toBe('CONSOLIDATED_REVIEW');

    submitAs($account, $application)->assertSessionHasNoErrors();
    expect(freshApplication($application)->status)->toBe(ApplicationStatus::ConsolidatedReview);
});

it('schedules a re-inspection as a new round, with a reason (T14)', function () {
    [$application, $account] = atConsolidatedReview();

    staffAction($this->registrar, $application, 'SCHEDULE_INSPECTION', ['inspections' => inspectionPlan($application)])->assertSessionHasErrors('action');
    staffAction($this->registrar, $application, 'SCHEDULE_INSPECTION', [
        'inspections' => inspectionPlan($application), 'comment' => 'Check the new fire equipment.',
    ])->assertSessionHasNoErrors();

    expect(freshApplication($application)->status)->toBe(ApplicationStatus::InspectionScheduled)
        ->and(Inspection::query()->where('application_id', $application->id)->where('round', 2)->count())->toBe(2)
        ->and(Inspection::query()->where('application_id', $application->id)->where('round', 1)->whereNotNull('signed_at')->count())->toBe(2)
        ->and(InspectionGuards::allSigned($application->id))->toBeFalse();
});
