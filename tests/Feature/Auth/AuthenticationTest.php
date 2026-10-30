<?php

declare(strict_types=1);

use App\Domain\Notifications\Mail\AccountLocked;
use App\Models\AuditActivity;
use App\Models\CompanyUser;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Laravel\Fortify\Features;

beforeEach(fn () => Mail::fake());

dataset('areas', [
    'staff' => ['admin', fn () => User::factory()->create()],
    'company' => ['portal', fn () => CompanyUser::factory()->create()],
]);

it('shows the login page', function (string $prefix) {
    $this->get(route("{$prefix}.login"))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component($prefix.'/auth/login'));
})->with(['admin', 'portal']);

it('logs a company account in to its dashboard', function () {
    $company = companyUser();

    $this->post(route('portal.login.store'), ['email' => $company->email, 'password' => 'password'])
        ->assertRedirect(route('portal.dashboard'));

    $this->assertAuthenticatedAs($company, 'company');
    expect($company->fresh()->last_login_at)->not->toBeNull();
});

it('sends staff with 2FA to the challenge', function () {
    $staff = staffUser();

    $this->post(route('admin.login.store'), ['email' => $staff->email, 'password' => 'password'])
        ->assertRedirect(route('admin.two-factor.login'))
        ->assertSessionHas('login.id', $staff->id);

    $this->assertGuest('web');
});

it('accepts the email in any case', function () {
    $company = companyUser(['email' => 'owner@example.com']);

    $this->post(route('portal.login.store'), ['email' => '  Owner@Example.COM', 'password' => 'password']);

    $this->assertAuthenticatedAs($company, 'company');
});

it('gives the same error for a wrong password and an unknown email', function (string $prefix, Closure $account) {
    $account = $account();

    $wrongPassword = $this->post(route("{$prefix}.login.store"), ['email' => $account->email, 'password' => 'wrong'])
        ->assertSessionHasErrors('email');
    $wrongMessage = session('errors')->first('email');

    $this->post(route("{$prefix}.login.store"), ['email' => 'nobody@example.com', 'password' => 'wrong'])
        ->assertSessionHasErrors(['email' => $wrongMessage]);

    expect($wrongMessage)->toBe(__('auth.failed'));
})->with('areas');

it('records successful and failed logins in the audit log', function () {
    $company = companyUser();

    $this->post(route('portal.login.store'), ['email' => $company->email, 'password' => 'wrong']);
    $this->post(route('portal.login.store'), ['email' => $company->email, 'password' => 'password']);

    expect(AuditActivity::query()->where('event', 'login_failed')->where('subject_id', $company->id)->exists())->toBeTrue()
        ->and(AuditActivity::query()->where('event', 'login')->where('subject_id', $company->id)->exists())->toBeTrue();
});

it('refuses an inactive account once the password is right', function (string $prefix, Closure $account) {
    $account = $account();
    $account->update(['status' => 'INACTIVE']);

    $this->post(route("{$prefix}.login.store"), ['email' => $account->email, 'password' => 'password'])
        ->assertSessionHasErrors(['email' => __('Your account is not active. Please contact SARIS.')]);
})->with('areas');

it('logs out', function () {
    $company = companyUser();

    $this->actingAs($company, 'company')
        ->post(route('portal.logout'))
        ->assertRedirect(route('portal.login'));

    $this->assertGuest('company');
});

it('logs an inactive account out on its next request', function () {
    $company = companyUser(['status' => 'INACTIVE']);

    $this->actingAs($company, 'company')
        ->get(route('portal.dashboard'))
        ->assertRedirect(route('portal.login'));

    $this->assertGuest('company');
});

it('keeps two-factor authentication on in Fortify', function () {
    expect(Features::canManageTwoFactorAuthentication())->toBeTrue();
});

/*
| Lockout (03 §7, §15 item 7; D-081)
*/

it('locks the account for 15 minutes after 5 failed attempts and emails once', function (string $prefix, Closure $account) {
    $account = $account();

    foreach (range(1, 5) as $attempt) {
        $this->post(route("{$prefix}.login.store"), ['email' => $account->email, 'password' => 'wrong']);
    }

    expect($account->fresh()->locked_until?->isFuture())->toBeTrue();
    Mail::assertQueued(AccountLocked::class, 1);

    // Wrong password while locked: still the generic message, no new email.
    $this->travel(2)->minutes(); // past the 5-per-minute throttle
    $this->post(route("{$prefix}.login.store"), ['email' => $account->email, 'password' => 'wrong'])
        ->assertSessionHasErrors(['email' => __('auth.failed')]);
    Mail::assertQueued(AccountLocked::class, 1);
})->with('areas');

it('says the account is locked only after the correct password', function () {
    $company = companyUser(['locked_until' => now()->addMinutes(10)]);

    $this->post(route('portal.login.store'), ['email' => $company->email, 'password' => 'password'])
        ->assertSessionHasErrors('email');

    expect(session('errors')->first('email'))->toContain('locked');
    $this->assertGuest('company');
});

it('unlocks by itself after 15 minutes', function () {
    $company = companyUser(['locked_until' => now()->addMinutes(15)]);

    $this->travel(16)->minutes();

    $this->post(route('portal.login.store'), ['email' => $company->email, 'password' => 'password'])
        ->assertRedirect(route('portal.dashboard'));
});

it('resets the failure count after a successful login', function () {
    $company = companyUser(['failed_login_attempts' => 4]);

    $this->post(route('portal.login.store'), ['email' => $company->email, 'password' => 'password']);

    expect($company->fresh()->failed_login_attempts)->toBe(0);
});

/*
| Temporary passwords (03 §4, §15 item 6; D-063)
*/

it('refuses an expired temporary password', function () {
    $company = CompanyUser::factory()->pendingFirstLogin('Temp-Pass-1234')->create();

    $this->travel(61)->minutes();

    $this->post(route('portal.login.store'), ['email' => $company->email, 'password' => 'Temp-Pass-1234'])
        ->assertSessionHasErrors(['email' => __('Your temporary password has expired. Request a new temporary password.')]);

    $this->assertGuest('company');
});

it('accepts a temporary password within the hour and verifies the email', function () {
    $company = CompanyUser::factory()->pendingFirstLogin('Temp-Pass-1234')->create();

    $this->travel(59)->minutes();

    $this->post(route('portal.login.store'), ['email' => $company->email, 'password' => 'Temp-Pass-1234'])
        ->assertRedirect(route('portal.dashboard'));

    expect($company->fresh()->email_verified_at)->not->toBeNull();
});

it('refuses an expired staff temporary password after 24 hours', function () {
    $staff = User::factory()->pendingFirstLogin('Temp-Pass-1234')->create();

    $this->travel(25)->hours();

    $this->post(route('admin.login.store'), ['email' => $staff->email, 'password' => 'Temp-Pass-1234'])
        ->assertSessionHasErrors('email');

    $this->assertGuest('web');
});
