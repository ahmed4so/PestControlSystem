<?php

declare(strict_types=1);

use App\Domain\Identity\Enums\AccountStatus;
use App\Domain\Identity\Enums\StaffRole;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->admin = tap(User::factory()->create())->assignRole(StaffRole::SystemAdministrator->value);
});

it('has no route that deletes a staff user', function () {
    $staff = User::factory()->create();

    $this->actingAs($this->admin)
        ->delete("/users/{$staff->id}")
        ->assertMethodNotAllowed();

    expect(User::withTrashed()->find($staff->id))->not->toBeNull()
        ->and($staff->fresh()->trashed())->toBeFalse();
});

it('deactivates a staff user and ends their sessions', function () {
    $staff = User::factory()->create();
    DB::table('sessions')->insert([
        'id' => 'session-of-staff',
        'user_id' => $staff->id,
        'payload' => '',
        'last_activity' => time(),
    ]);

    $this->actingAs($this->admin)
        ->post(route('users.deactivate', $staff))
        ->assertSessionHas('success');

    expect($staff->fresh()->status)->toBe(AccountStatus::Inactive)
        ->and(DB::table('sessions')->where('user_id', $staff->id)->exists())->toBeFalse();
});

it('logs a deactivated user out on their next request', function () {
    $staff = User::factory()->create();

    $this->actingAs($this->admin)->post(route('users.deactivate', $staff));

    $this->actingAs($staff->fresh())
        ->get(route('dashboard'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

it('reactivates a deactivated staff user', function () {
    $staff = User::factory()->inactive()->create();

    $this->actingAs($this->admin)->post(route('users.activate', $staff));

    expect($staff->fresh()->status)->toBe(AccountStatus::Active);
});

it('does not let a user deactivate their own account', function () {
    $this->actingAs($this->admin)
        ->post(route('users.deactivate', $this->admin))
        ->assertSessionHas('error');

    expect($this->admin->fresh()->status)->toBe(AccountStatus::Active);
});

it('requires the suspend user permission to deactivate', function () {
    $staff = User::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('users.deactivate', $staff))
        ->assertForbidden();

    expect($staff->fresh()->status)->toBe(AccountStatus::Active);
});

it('creates staff users with a forced password change on first login', function () {
    $this->actingAs($this->admin)->post(route('users.store'), [
        'name' => 'New Staff',
        'email' => 'new.staff@example.com',
        'password' => 'Str0ng!Passw0rd#2026',
        'password_confirmation' => 'Str0ng!Passw0rd#2026',
    ])->assertSessionHasNoErrors();

    $staff = User::query()->where('email', 'new.staff@example.com')->firstOrFail();

    expect($staff->first_login)->toBeTrue()
        ->and($staff->status)->toBe(AccountStatus::Active);
});
