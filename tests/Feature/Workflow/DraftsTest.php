<?php

declare(strict_types=1);

use App\Domain\Applications\Actions\DiscardStaleDraft;
use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Applications\Enums\LicenseCategory;
use App\Domain\Identity\Enums\StaffRole;
use App\Domain\Workflow\Enums\WorkflowActionCode;
use App\Domain\Workflow\Exceptions\TransitionRefused;
use App\Models\Application;
use App\Models\AuditActivity;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\UniqueConstraintViolationException;
use Spatie\Permission\Models\Role;

/*
 * Drafts (D-044, D-187, D-193, D-194): a draft holds the open key from
 * creation; only the System Administrator discards it, and only when it was
 * never submitted and is untouched for the configured period.
 */

function discardRefusal(Application $application, User $user): ?string
{
    try {
        app(DiscardStaleDraft::class)->handle($application, $user);
    } catch (TransitionRefused $refused) {
        return $refused->reasons[0];
    }

    return null;
}

it('blocks a second draft of the same service and category, not of another category', function () {
    $draft = Application::factory()->create();

    expect(fn () => Application::factory()->create(['company_id' => $draft->company_id]))
        ->toThrow(UniqueConstraintViolationException::class);

    Application::factory()->category(LicenseCategory::Wholesaler)->create(['company_id' => $draft->company_id]);
    expect(Application::query()->where('company_id', $draft->company_id)->count())->toBe(2);
});

it('lets the System Administrator discard a stale draft, which frees the slot', function () {
    $draft = Application::factory()->create();
    $this->travel(31)->days();

    expect(discardRefusal($draft, systemAdministrator()))->toBeNull();

    $discarded = Application::query()->withTrashed()->findOrFail($draft->id);
    expect($discarded->trashed())->toBeTrue()
        ->and($discarded->open_key)->toBeNull()
        ->and(AuditActivity::query()->where('event', 'draft_discarded')->where('subject_id', $draft->id)->exists())->toBeTrue();

    Application::factory()->create(['company_id' => $draft->company_id]);
    expect(Application::query()->where('company_id', $draft->company_id)->count())->toBe(1);
});

it('refuses to discard a draft changed within the period', function () {
    $draft = Application::factory()->create();
    $this->travel(29)->days();

    expect(discardRefusal($draft, systemAdministrator()))->toBe(__(DiscardStaleDraft::REASON_RECENT, ['days' => 30]));
    expect($draft->fresh()->trashed())->toBeFalse();
});

it('follows the configured period (D-194)', function () {
    config(['applications.stale_draft_days' => 10]);
    $draft = Application::factory()->create();
    $this->travel(11)->days();

    expect(discardRefusal($draft, systemAdministrator()))->toBeNull();
});

it('never discards a submitted or returned application', function () {
    $application = submittedApplication();
    $this->travel(60)->days();

    expect(discardRefusal($application, systemAdministrator()))->toBe(DiscardStaleDraft::REASON_SUBMITTED);
    expect($application->fresh()->trashed())->toBeFalse();
});

it('refuses other staff', function (string $role) {
    $draft = Application::factory()->create();
    $this->travel(31)->days();

    expect(discardRefusal($draft, staffWithRole($role)))->toBe(DiscardStaleDraft::REASON_PERMISSION);
    expect($draft->fresh()->trashed())->toBeFalse();
})->with(['REGISTRAR', 'DIRECTOR_GENERAL']);

it('gives the discard permission to the System Administrator only', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    expect(Role::query()->whereHas('permissions', fn ($query) => $query->where('name', DiscardStaleDraft::PERMISSION))->pluck('name')->all())
        ->toBe([StaffRole::SystemAdministrator->value]);
});

it('cannot act on a discarded draft', function () {
    loadTestWorkflow();
    $draft = Application::factory()->create();
    $owner = ownerOf($draft);
    $this->travel(31)->days();
    app(DiscardStaleDraft::class)->handle($draft, systemAdministrator());

    expect(fn () => act($draft, WorkflowActionCode::Submit, $owner, declaration()))
        ->toThrow(ModelNotFoundException::class);
    expect(Application::query()->withTrashed()->findOrFail($draft->id)->status)->toBe(ApplicationStatus::Draft);
});
