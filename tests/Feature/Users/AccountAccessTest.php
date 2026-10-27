<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

test('inactive accounts are logged out of the dashboard', function () {
    $user = User::factory()->suspendedAccount()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

test('users on first login are sent to change their password', function () {
    $user = User::factory()->create(['first_login' => true]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('user-password.edit'));
});

test('users without the permission cannot manage users', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('users.index'))
        ->assertForbidden();
});

test('super admins can manage users and roles', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    $this->actingAs($admin)->get(route('users.index'))->assertOk();
    $this->actingAs($admin)->get(route('roles.index'))->assertOk();
});
