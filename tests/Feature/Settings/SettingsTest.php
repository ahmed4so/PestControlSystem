<?php

declare(strict_types=1);

/*
| Account settings in both portals.
*/

it('shows the settings pages', function (string $prefix, string $guard) {
    $account = $prefix === 'admin' ? staffUser() : companyUser();

    foreach (['profile.edit', 'user-password.edit'] as $route) {
        $this->actingAs($account, $guard)->get(route("{$prefix}.{$route}"))->assertOk();
    }

    $this->actingAs($account, $guard)
        ->withSession(passwordConfirmed())
        ->get(route("{$prefix}.two-factor.show"))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component("{$prefix}/settings/two-factor")
            ->where('canDisable', $prefix === 'portal'));
})->with([['admin', 'web'], ['portal', 'company']]);

it('updates the staff profile but never the login email (D-172)', function () {
    $staff = staffUser();

    $this->actingAs($staff)->patch(route('admin.profile.update'), [
        'name' => 'Updated Name',
        'email' => 'changed@example.com',
        'phone' => '0612345678',
        'preferred_locale' => 'so',
    ])->assertSessionHasNoErrors()->assertRedirect(route('admin.profile.edit'));

    $staff->refresh();
    expect($staff->name)->toBe('Updated Name')
        ->and($staff->email)->not->toBe('changed@example.com')
        ->and($staff->phone)->toBe('+252612345678')
        ->and($staff->preferred_locale)->toBe('so');
});

it('updates the company account details but not the login email', function () {
    $company = companyUser();

    $this->actingAs($company, 'company')->patch(route('portal.profile.update'), [
        'full_name' => 'Hodan Warsame',
        'email' => 'changed@example.com',
        'phone' => '+252 61 765 4321',
        'preferred_locale' => 'so',
    ])->assertSessionHasNoErrors();

    $company->refresh();
    expect($company->full_name)->toBe('Hodan Warsame')
        ->and($company->email)->not->toBe('changed@example.com')
        ->and($company->phone)->toBe('+252617654321');
});
