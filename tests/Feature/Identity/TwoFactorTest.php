<?php

declare(strict_types=1);

use App\Domain\Notifications\Mail\TwoFactorChanged;
use App\Models\AuditActivity;
use App\Models\CompanyUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use PragmaRX\Google2FA\Google2FA;

/*
| Two-factor authentication (03 §8, §15 item 8; D-052, D-064, D-087).
*/

beforeEach(fn () => Mail::fake());

/**
 * Gives an account a real TOTP secret and returns it.
 */
function withRealTwoFactor(User|CompanyUser $account): string
{
    $secret = app(Google2FA::class)->generateSecretKey();

    $account->forceFill([
        'two_factor_secret' => encrypt($secret),
        'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1', 'recovery-code-2'])),
        'two_factor_confirmed_at' => now(),
    ])->save();

    return $secret;
}

it('keeps staff without confirmed 2FA out of every admin page', function () {
    $staff = User::factory()->create(); // no 2FA

    foreach (['admin.dashboard', 'admin.users.index', 'admin.roles.index', 'admin.profile.edit', 'admin.failed-emails.index'] as $route) {
        $this->actingAs($staff)
            ->get(route($route))
            ->assertRedirect(route('admin.two-factor.show'));
    }
});

it('lets staff without 2FA reach the set-up, password and logout routes', function () {
    $staff = User::factory()->create();

    $this->actingAs($staff)->withSession(passwordConfirmed())->get(route('admin.two-factor.show'))->assertOk();
    $this->actingAs($staff)->get(route('admin.user-password.edit'))->assertOk();
    $this->actingAs($staff)->withSession(passwordConfirmed())->post(route('admin.two-factor.enable'))->assertRedirect();
    $this->actingAs($staff)->post(route('admin.logout'))->assertRedirect(route('admin.login'));
});

it('lets staff with confirmed 2FA in', function () {
    $this->actingAs(staffUser())->get(route('admin.dashboard'))->assertOk();
});

it('lets local development switch the staff 2FA requirement off (D-185)', function () {
    $this->app->detectEnvironment(fn () => 'local');
    config(['identity.staff_two_factor_required' => false]);

    $this->actingAs(User::factory()->create(), 'web')
        ->get(route('admin.dashboard'))
        ->assertOk();
});

it('enforces staff 2FA outside local development, whatever the setting says', function (string $environment) {
    $this->app->detectEnvironment(fn () => $environment);
    config(['identity.staff_two_factor_required' => false]);

    $this->actingAs(User::factory()->create(), 'web')
        ->get(route('admin.dashboard'))
        ->assertRedirect(route('admin.two-factor.show'));
})->with(['production', 'staging', 'testing']);

it('still enforces staff 2FA locally when the setting is on', function () {
    $this->app->detectEnvironment(fn () => 'local');
    config(['identity.staff_two_factor_required' => true]);

    $this->actingAs(User::factory()->create(), 'web')
        ->get(route('admin.dashboard'))
        ->assertRedirect(route('admin.two-factor.show'));
});

it('completes the staff login with a valid code', function () {
    $staff = User::factory()->create();
    $secret = withRealTwoFactor($staff);

    $this->post(route('admin.login.store'), ['email' => $staff->email, 'password' => 'password'])
        ->assertRedirect(route('admin.two-factor.login'));

    $this->get(route('admin.two-factor.login'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('admin/auth/two-factor-challenge'));

    $this->post(route('admin.two-factor.login.store'), ['code' => app(Google2FA::class)->getCurrentOtp($secret)])
        ->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($staff, 'web');
    $this->assertGuest('company');
});

it('sends a wrong code back to the same portal challenge', function () {
    $company = companyUser();
    withRealTwoFactor($company);

    $this->post(route('portal.login.store'), ['email' => $company->email, 'password' => 'password'])
        ->assertRedirect(route('portal.two-factor.login'));

    $this->post(route('portal.two-factor.login.store'), ['code' => '000000'])
        ->assertRedirect(route('portal.two-factor.login'))
        ->assertSessionHasErrors('code');

    $this->assertGuest('company');
});

it('logs a company with 2FA in to the company guard', function () {
    $company = companyUser();
    $secret = withRealTwoFactor($company);

    $this->post(route('portal.login.store'), ['email' => $company->email, 'password' => 'password']);
    $this->post(route('portal.two-factor.login.store'), ['code' => app(Google2FA::class)->getCurrentOtp($secret)])
        ->assertRedirect(route('portal.dashboard'));

    $this->assertAuthenticatedAs($company, 'company');
    $this->assertGuest('web');
});

it('sends a challenge page without a pending login to that portal login', function () {
    $this->get(route('portal.two-factor.login'))->assertRedirect(route('portal.login'));
    $this->get(route('admin.two-factor.login'))->assertRedirect(route('admin.login'));
});

it('does not let staff disable 2FA', function () {
    expect(app('router')->getRoutes()->hasNamedRoute('admin.two-factor.disable'))->toBeFalse();

    $this->actingAs(staffUser())
        ->withSession(passwordConfirmed())
        ->delete('/admin/user/two-factor-authentication')
        ->assertMethodNotAllowed();
});

it('lets a company disable 2FA after password confirmation, and tells them', function () {
    $company = companyUser();
    withRealTwoFactor($company);

    $this->actingAs($company, 'company')
        ->delete(route('portal.two-factor.disable'))
        ->assertRedirect(route('portal.password.confirm'));

    $this->actingAs($company, 'company')
        ->withSession(passwordConfirmed())
        ->delete(route('portal.two-factor.disable'))
        ->assertRedirect();

    expect($company->fresh()->two_factor_secret)->toBeNull();
    Mail::assertQueued(TwoFactorChanged::class, fn ($mail) => $mail->data['change'] === 'disabled');
});

it('emails and audits when 2FA is confirmed', function () {
    $company = companyUser();

    $this->actingAs($company, 'company')->withSession(passwordConfirmed())->post(route('portal.two-factor.enable'));
    $secret = decrypt($company->fresh()->two_factor_secret);

    $this->actingAs($company, 'company')
        ->withSession(passwordConfirmed())
        ->post(route('portal.two-factor.confirm'), ['code' => app(Google2FA::class)->getCurrentOtp($secret)])
        ->assertSessionHasNoErrors();

    expect($company->fresh()->two_factor_confirmed_at)->not->toBeNull()
        ->and(AuditActivity::query()->where('event', 'two_factor_enabled')->exists())->toBeTrue();
    Mail::assertQueued(TwoFactorChanged::class, fn ($mail) => $mail->data['change'] === 'enabled');
});

it('asks for the password again on the 2FA page after 15 minutes for staff', function () {
    $staff = staffUser();

    $this->actingAs($staff)
        ->withSession(['auth.password_confirmed_at' => time() - 16 * 60])
        ->get(route('admin.two-factor.show'))
        ->assertRedirect(route('admin.password.confirm'));

    $this->actingAs($staff)
        ->withSession(['auth.password_confirmed_at' => time() - 14 * 60])
        ->get(route('admin.two-factor.show'))
        ->assertOk();
});

it('keeps the company confirmation valid for 30 minutes', function () {
    $company = companyUser();

    $this->actingAs($company, 'company')
        ->withSession(['auth.password_confirmed_at' => time() - 25 * 60])
        ->get(route('portal.two-factor.show'))
        ->assertOk();

    $this->actingAs($company, 'company')
        ->withSession(['auth.password_confirmed_at' => time() - 31 * 60])
        ->get(route('portal.two-factor.show'))
        ->assertRedirect(route('portal.password.confirm'));
});

it('confirms the password in the right portal', function () {
    $company = companyUser();

    $this->actingAs($company, 'company')
        ->post(route('portal.password.confirm.store'), ['password' => 'password'])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('auth.password_confirmed_at');

    $this->actingAs($company, 'company')
        ->post(route('portal.password.confirm.store'), ['password' => 'wrong'])
        ->assertSessionHasErrors('password');
});

it('lets the System Administrator reset a staff member 2FA', function () {
    $admin = systemAdministrator();
    $staff = staffUser();
    DB::table('sessions')->insert(['id' => 'staff-session', 'user_id' => $staff->id, 'payload' => '', 'last_activity' => time()]);

    $this->actingAs($admin)
        ->withSession(passwordConfirmed())
        ->post(route('admin.users.reset-two-factor', $staff))
        ->assertSessionHas('success');

    $staff->refresh();
    expect($staff->two_factor_secret)->toBeNull()
        ->and($staff->two_factor_confirmed_at)->toBeNull()
        ->and(DB::table('sessions')->where('user_id', $staff->id)->exists())->toBeFalse()
        ->and(AuditActivity::query()->where('event', 'two_factor_reset')->where('causer_id', $admin->id)->exists())->toBeTrue();
    Mail::assertQueued(TwoFactorChanged::class, fn ($mail) => $mail->data['change'] === 'reset');

    $this->actingAs($staff)->get(route('admin.dashboard'))->assertRedirect(route('admin.two-factor.show'));
});

it('refuses a 2FA reset without the permission', function () {
    $staff = staffUser();

    $this->actingAs(staffUser())
        ->withSession(passwordConfirmed())
        ->post(route('admin.users.reset-two-factor', $staff))
        ->assertForbidden();

    expect($staff->fresh()->two_factor_confirmed_at)->not->toBeNull();
});

it('refuses a 2FA reset without a recent password confirmation', function () {
    $staff = staffUser();

    $this->actingAs(systemAdministrator())
        ->post(route('admin.users.reset-two-factor', $staff))
        ->assertRedirect(route('admin.password.confirm'));

    expect($staff->fresh()->two_factor_confirmed_at)->not->toBeNull();
});
