<?php

declare(strict_types=1);

use App\Domain\Identity\Enums\AccountStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->admin = systemAdministrator();
});

it('has no route that deletes a staff user', function () {
    $staff = User::factory()->create();

    $this->actingAs($this->admin)
        ->withSession(passwordConfirmed())
        ->delete("/admin/users/{$staff->id}")
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
        ->withSession(passwordConfirmed())
        ->post(route('admin.users.deactivate', $staff))
        ->assertSessionHas('success');

    expect($staff->fresh()->status)->toBe(AccountStatus::Inactive)
        ->and(DB::table('sessions')->where('user_id', $staff->id)->exists())->toBeFalse();
});

it('logs a deactivated user out on their next request', function () {
    $staff = User::factory()->create();

    $this->actingAs($this->admin)->withSession(passwordConfirmed())->post(route('admin.users.deactivate', $staff));

    $this->actingAs($staff->fresh())
        ->get(route('admin.dashboard'))
        ->assertRedirect(route('admin.login'));

    $this->assertGuest();
});

it('reactivates a deactivated staff user', function () {
    $staff = User::factory()->inactive()->create();

    $this->actingAs($this->admin)->withSession(passwordConfirmed())->post(route('admin.users.activate', $staff));

    expect($staff->fresh()->status)->toBe(AccountStatus::Active);
});

it('does not let a user deactivate their own account', function () {
    $this->actingAs($this->admin)
        ->withSession(passwordConfirmed())
        ->post(route('admin.users.deactivate', $this->admin))
        ->assertSessionHas('error');

    expect($this->admin->fresh()->status)->toBe(AccountStatus::Active);
});

it('requires the suspend user permission to deactivate', function () {
    $staff = User::factory()->create();

    $this->actingAs(staffUser())
        ->withSession(passwordConfirmed())
        ->post(route('admin.users.deactivate', $staff))
        ->assertForbidden();

    expect($staff->fresh()->status)->toBe(AccountStatus::Active);
});
