<?php

declare(strict_types=1);

use App\Domain\Identity\Enums\StaffRole;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('seeds only the System Administrator and the D-023 roles', function () {
    expect(Role::query()->pluck('name')->sort()->values()->all())
        ->toBe(collect(StaffRole::cases())->map->value->sort()->values()->all());
});

it('removes an existing Super Admin role when seeding', function () {
    Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);

    $this->seed(RolesAndPermissionsSeeder::class);

    expect(Role::query()->where('name', 'Super Admin')->exists())->toBeFalse();
});

it('lets nobody bypass permissions, not even the System Administrator', function () {
    $admin = systemAdministrator();

    expect($admin->can('manage users anywhere'))->toBeFalse()
        ->and($admin->can('view applications'))->toBeFalse()
        ->and($admin->can('approve enforcement'))->toBeFalse()
        ->and($admin->can('view user'))->toBeTrue();
});

it('gives the System Administrator no workflow permissions', function () {
    $permissions = Role::findByName(StaffRole::SystemAdministrator->value)->permissions->pluck('name');

    foreach (['view applications', 'view assigned applications', 'verify products', 'request enforcement', 'approve enforcement'] as $workflowPermission) {
        expect($permissions)->not->toContain($workflowPermission);
    }
});

it('refuses to combine the System Administrator role with a workflow role', function () {
    $staff = User::factory()->create();
    $roleIds = Role::query()
        ->whereIn('name', [StaffRole::SystemAdministrator->value, StaffRole::PesticideRegistrar->value])
        ->pluck('id')
        ->all();

    $this->actingAs(systemAdministrator())
        ->withSession(passwordConfirmed())
        ->post(route('admin.roles.assign.store'), ['user_id' => $staff->id, 'role_ids' => $roleIds])
        ->assertSessionHasErrors('role_ids');

    expect($staff->fresh()->roles)->toBeEmpty();
});

it('does not let the designed roles be deleted or renamed', function () {
    $registrar = Role::findByName(StaffRole::PesticideRegistrar->value);

    $admin = systemAdministrator();

    $this->actingAs($admin)
        ->withSession(passwordConfirmed())
        ->delete(route('admin.roles.destroy', $registrar))
        ->assertRedirect(route('admin.roles.index'));

    $this->actingAs($admin)
        ->withSession(passwordConfirmed())
        ->put(route('admin.roles.update', $registrar), ['name' => 'Registrar'])
        ->assertSessionHasErrors('name');

    expect(Role::query()->where('name', StaffRole::PesticideRegistrar->value)->exists())->toBeTrue();
});

it('maps every workflow role to its actor code', function () {
    expect(StaffRole::fromCode('REGISTRAR'))->toBe(StaffRole::PesticideRegistrar)
        ->and(StaffRole::fromCode('DIRECTOR_GENERAL'))->toBe(StaffRole::DirectorGeneral)
        ->and(StaffRole::SystemAdministrator->code())->toBeNull();
});
