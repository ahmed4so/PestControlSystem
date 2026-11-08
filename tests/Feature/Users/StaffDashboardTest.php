<?php

declare(strict_types=1);

use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Applications\Enums\LicenseCategory;
use App\Models\Application;
use App\Models\WorkflowTemplate;
use Database\Seeders\RolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * The staff dashboard shows the member's roles and, for workflow roles,
 * the size of "My work"; the System Administrator gets no application data.
 */

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('tells a System Administrator that applications need a workflow role', function () {
    $this->actingAs(systemAdministrator(), 'web')
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/dashboard')
            ->where('roles', ['System Administrator'])
            ->where('myWork', null)
            ->where('workflowRoles', fn ($roles) => collect($roles)->contains('Pesticide Registrar')
                && ! collect($roles)->contains('System Administrator')));
});

it('shows a Registrar how many applications wait in My work', function () {
    $this->artisan('workflows:load')->assertSuccessful();
    $template = WorkflowTemplate::query()->where('code', 'COMPANY_LICENSE_V1')->sole();
    Application::factory()->category(LicenseCategory::Importer)->status(ApplicationStatus::Submitted)->create([
        'workflow_template_id' => $template->id,
        'reference' => 'CL-2026-00001',
    ]);

    $this->actingAs(staffWithRole('REGISTRAR'), 'web')
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('roles', ['Pesticide Registrar'])->where('myWork', 1));
});
