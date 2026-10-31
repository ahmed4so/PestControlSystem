<?php

declare(strict_types=1);

use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Workflow\Enums\WorkflowActionCode;
use App\Models\Application;
use App\Models\ApplicationVersion;
use App\Models\WorkflowAction;

/*
 * History never changes (02 §4.6–§4.7, D-042, D-187), and a company sees
 * only its own applications (D-048).
 */

it('refuses to change or delete a version', function () {
    $version = submittedApplication()->currentVersion();

    expect(fn () => $version->update(['declaration_name' => 'Someone Else']))->toThrow(LogicException::class, 'cannot be changed')
        ->and(fn () => $version->delete())->toThrow(LogicException::class, 'cannot be deleted')
        ->and(ApplicationVersion::query()->findOrFail($version->id)->declaration_name)->toBe('Amina Hassan');
});

it('refuses to change or delete a workflow action', function () {
    $application = submittedApplication();
    $action = WorkflowAction::query()->where('application_id', $application->id)->sole();

    expect(fn () => $action->update(['comment' => 'edited']))->toThrow(LogicException::class, 'cannot be changed')
        ->and(fn () => $action->delete())->toThrow(LogicException::class, 'cannot be deleted')
        ->and(WorkflowAction::query()->whereKey($action->id)->exists())->toBeTrue();
});

it('keeps every action of an application in order', function () {
    $application = advanceTo(submittedApplication(), ApplicationStatus::TechnicalDocReview);

    expect(WorkflowAction::query()->where('application_id', $application->id)->orderBy('id')->pluck('action')->map->value->all())
        ->toBe(['SUBMIT', 'ACKNOWLEDGE', 'COMPLETE_ADMIN_REVIEW']);
});

it('shows a company only its own applications', function () {
    $mine = companyUser();
    $own = Application::factory()->create(['company_id' => $mine->company_id]);
    $other = Application::factory()->create();

    // A Company Portal request puts the test in the company area (D-048).
    $this->actingAs($mine, 'company')->get(route('portal.dashboard'))->assertOk();

    expect(Application::query()->find($other->id))->toBeNull()
        ->and(Application::query()->find($own->id))->not->toBeNull()
        ->and(Application::query()->pluck('id')->all())->toBe([$own->id]);
});

it('records the IP of every action', function () {
    $application = submittedApplication();
    act($application, WorkflowActionCode::Acknowledge, staffWithRole('REGISTRAR'));

    expect(WorkflowAction::query()->where('application_id', $application->id)->pluck('ip_address')->unique()->all())
        ->toBe(['127.0.0.1']);
});
