<?php

declare(strict_types=1);

use App\Domain\Identity\Enums\StaffRole;
use App\Domain\Notifications\Mail\CompanyTempPassword;
use App\Domain\Notifications\Mail\StaffTempPassword;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use PragmaRX\Google2FA\Google2FA;

/*
| Phase 1 "Done when" (09 §3): the two journeys end to end.
*/

beforeEach(fn () => Mail::fake());

function emailedPassword(string $mailable): string
{
    $password = null;
    Mail::assertQueued($mailable, function ($mail) use (&$password) {
        $password = $mail->data['password'];

        return true;
    });

    return (string) $password;
}

it('lets a company sign up, log in with the temporary password, set a password and reach an empty dashboard', function () {
    $this->post(route('portal.signup.store'), [
        'full_name' => 'Amina Hassan',
        'email' => 'amina@example.com',
        'phone' => '0612345678',
        'consent' => '1',
        ...honeypotFields(),
    ])->assertRedirect();

    $temporary = emailedPassword(CompanyTempPassword::class);

    $this->post(route('portal.login.store'), ['email' => 'amina@example.com', 'password' => $temporary])
        ->assertRedirect(route('portal.dashboard'));
    $this->get(route('portal.dashboard'))->assertRedirect(route('portal.user-password.edit'));

    $this->put(route('portal.user-password.update'), [
        'current_password' => $temporary,
        'password' => 'Amina-Own-Pass-1!',
        'password_confirmation' => 'Amina-Own-Pass-1!',
    ])->assertSessionHasNoErrors()->assertRedirect(route('portal.dashboard'));

    $this->get(route('portal.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('portal/dashboard')->where('auth.user.email', 'amina@example.com'));
});

it('lets a staff member get a temporary password, set a password, enrol 2FA and reach the admin dashboard', function () {
    $admin = systemAdministrator();

    $this->actingAs($admin)->withSession(passwordConfirmed())->post(route('admin.users.store'), [
        'name' => 'Faadumo Ali',
        'email' => 'faadumo@saris.gov.so',
        'preferred_locale' => 'en',
        'roles' => [StaffRole::PesticideRegistrar->value],
    ])->assertSessionHasNoErrors();

    auth('web')->logout();
    $this->flushSession();
    $temporary = emailedPassword(StaffTempPassword::class);

    $this->post(route('admin.login.store'), ['email' => 'faadumo@saris.gov.so', 'password' => $temporary]);
    $this->put(route('admin.user-password.update'), [
        'current_password' => $temporary,
        'password' => 'Faadumo-Own-Pass-1!',
        'password_confirmation' => 'Faadumo-Own-Pass-1!',
    ])->assertSessionHasNoErrors();

    $this->get(route('admin.dashboard'))->assertRedirect(route('admin.two-factor.show'));

    // Enrol 2FA (the password was just confirmed by the change).
    $this->post(route('admin.two-factor.enable'))->assertRedirect();
    $staff = User::query()->where('email', 'faadumo@saris.gov.so')->firstOrFail();
    $code = app(Google2FA::class)->getCurrentOtp(decrypt($staff->two_factor_secret));
    $this->post(route('admin.two-factor.confirm'), ['code' => $code])->assertSessionHasNoErrors();

    $this->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('admin/dashboard'));
});
