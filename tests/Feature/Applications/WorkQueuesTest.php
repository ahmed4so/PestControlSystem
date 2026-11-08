<?php

declare(strict_types=1);

use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Applications\Enums\LicenseCategory;
use App\Models\Application;
use App\Models\Company;
use App\Models\Delegation;
use App\Models\StageAssignment;
use App\Models\User;
use App\Models\WorkflowTemplate;
use Database\Seeders\RolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * Staff work queues (04 §15, D-105).
 */

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->artisan('workflows:load')->assertSuccessful();
    $this->template = WorkflowTemplate::query()->where('code', 'COMPANY_LICENSE_V1')->sole();
});

function queued(ApplicationStatus $status, array $attributes = [], LicenseCategory $category = LicenseCategory::Importer): Application
{
    return Application::factory()->category($category)->status($status)->create([
        'workflow_template_id' => test()->template->id,
        'reference' => 'CL-2026-'.str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT),
        ...$attributes,
    ]);
}

function queueIds(User $user, string $tab = 'mine'): array
{
    $ids = [];

    test()->actingAs($user, 'web')
        ->get(route('admin.queues', ['tab' => $tab]))
        ->assertOk()
        ->assertInertia(function (Assert $page) use (&$ids) {
            $ids = collect($page->toArray()['props']['items']['data'] ?? [])->pluck('id')->all();
        });

    return $ids;
}

it('shows each role the applications at its stages in "My work"', function () {
    $submitted = queued(ApplicationStatus::Submitted);
    $director = queued(ApplicationStatus::DirectorReview, category: LicenseCategory::Retailer);
    queued(ApplicationStatus::Draft, ['workflow_template_id' => null, 'reference' => null], LicenseCategory::Wholesaler);

    expect(queueIds(staffWithRole('REGISTRAR')))->toBe([$submitted->id])
        ->and(queueIds(staffWithRole('DEPARTMENTAL_DIRECTOR')))->toBe([$director->id])
        ->and(queueIds(staffWithRole('PAYMENT_OFFICER')))->toBe([]);
});

it('shows a Technical Reviewer only the reviews assigned to them', function () {
    $reviewer = staffWithRole('TECHNICAL_REVIEWER');
    $assigned = queued(ApplicationStatus::TechnicalDocReview);
    queued(ApplicationStatus::TechnicalDocReview, category: LicenseCategory::Retailer);
    $assignment = new StageAssignment([
        'stage' => 'TECHNICAL_DOC_REVIEW',
        'assigned_user_id' => $reviewer->id, 'assigned_by' => staffWithRole('REGISTRAR')->id, 'assigned_at' => now(),
    ]);
    $assignment->application()->associate($assigned)->save();

    expect(queueIds($reviewer))->toBe([$assigned->id]);

    $this->actingAs($reviewer, 'web')
        ->get(route('admin.queues', ['tab' => 'waiting']))
        ->assertInertia(fn (Assert $page) => $page->where('tab', 'mine')->where('tabs', ['mine']));
});

it('gives the DG\'s work to an active delegate (D-027)', function () {
    $decision = queued(ApplicationStatus::DgDecision);
    $delegate = staffWithRole('DEPARTMENTAL_DIRECTOR');

    expect(queueIds($delegate))->toBe([]);

    Delegation::factory()->create(['delegator_id' => staffWithRole('DIRECTOR_GENERAL')->id, 'delegate_id' => $delegate->id]);

    expect(queueIds($delegate))->toBe([$decision->id]);
});

it('lists applications waiting on the company with the days waiting', function () {
    $returned = queued(ApplicationStatus::Returned);
    queued(ApplicationStatus::AdminReview, category: LicenseCategory::Retailer);

    $this->actingAs(staffWithRole('REGISTRAR'), 'web')
        ->get(route('admin.queues', ['tab' => 'waiting']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('items.data.0.id', $returned->id)
            ->count('items.data', 1)
            ->where('items.data.0.days', 0));
});

it('flags applications of companies that must submit an amendment', function () {
    $company = Company::factory()->create(['amendment_required_at' => now()]);
    $flagged = queued(ApplicationStatus::AdminReview, ['company_id' => $company->id]);
    queued(ApplicationStatus::AdminReview, category: LicenseCategory::Retailer);

    $this->actingAs(staffWithRole('REGISTRAR'), 'web')
        ->get(route('admin.queues', ['tab' => 'flags']))
        ->assertInertia(fn (Assert $page) => $page
            ->count('items.data', 1)
            ->where('items.data.0.id', $flagged->id)
            ->where('items.data.0.flags', ['Amendment required']));
});

it('counts applications per stage for the Registrar, Director and DG', function () {
    queued(ApplicationStatus::AdminReview);
    queued(ApplicationStatus::AdminReview, category: LicenseCategory::Retailer);
    queued(ApplicationStatus::DgDecision, category: LicenseCategory::Wholesaler);

    $this->actingAs(staffWithRole('DIRECTOR_GENERAL'), 'web')
        ->get(route('admin.queues', ['tab' => 'stages']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('stages.0.status', 'ADMIN_REVIEW')
            ->where('stages.0.count', 2)
            ->where('stages.1.status', 'DG_DECISION')
            ->where('stages.1.count', 1));

    $this->actingAs(staffWithRole('PAYMENT_OFFICER'), 'web')
        ->get(route('admin.queues', ['tab' => 'stages']))
        ->assertInertia(fn (Assert $page) => $page->where('tab', 'mine')->where('tabs', ['mine', 'waiting', 'flags']));
});

it('keeps companies and staff without a workflow permission out', function () {
    $this->actingAs(companyUser(), 'company')->get(route('admin.queues'))->assertRedirect();
    $this->actingAs(systemAdministrator(), 'web')->get(route('admin.queues'))->assertForbidden();
});
