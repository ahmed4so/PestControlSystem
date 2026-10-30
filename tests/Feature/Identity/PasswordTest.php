<?php

declare(strict_types=1);

use App\Domain\Notifications\Mail\PasswordChanged;
use App\Domain\Notifications\Mail\PasswordExpiryReminder;
use App\Domain\Notifications\Mail\PasswordResetLink;
use App\Models\CompanyUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Testing\TestResponse;

/*
| Passwords in both portals (03 §6, §7; D-081, D-082).
*/

beforeEach(fn () => Mail::fake());

function changePassword(User|CompanyUser $account, string $current, string $new): TestResponse
{
    $prefix = $account instanceof User ? 'admin' : 'portal';
    $guard = $account instanceof User ? 'web' : 'company';

    return test()->actingAs($account, $guard)->put(route("{$prefix}.user-password.update"), [
        'current_password' => $current,
        'password' => $new,
        'password_confirmation' => $new,
    ]);
}

it('sends the reset link email with the right portal URL', function (string $prefix) {
    $account = $prefix === 'admin' ? staffUser() : companyUser();

    $this->post(route("{$prefix}.password.email"), ['email' => $account->email, ...honeypotFields()]);

    Mail::assertQueued(PasswordResetLink::class, fn (PasswordResetLink $mail) => str_contains(
        $mail->data['url'],
        $prefix === 'admin' ? '/admin/reset-password/' : '/reset-password/'
    ) && ($prefix === 'admin' || ! str_contains($mail->data['url'], '/admin/')));
})->with(['admin', 'portal']);

it('resets a company password, unlocks the account and ends its sessions', function () {
    $account = companyUser(['locked_until' => now()->addMinutes(10), 'failed_login_attempts' => 3]);
    DB::table('company_sessions')->insert(['id' => 'old-session', 'user_id' => $account->id, 'payload' => '', 'last_activity' => time()]);
    $token = Password::broker('company_users')->createToken($account);

    $this->post(route('portal.password.update'), [
        'token' => $token,
        'email' => $account->email,
        'password' => 'Brand-New-Pass1!',
        'password_confirmation' => 'Brand-New-Pass1!',
    ])->assertRedirect(route('portal.login'));

    $account->refresh();
    expect(Hash::check('Brand-New-Pass1!', $account->password))->toBeTrue()
        ->and($account->locked_until)->toBeNull()
        ->and($account->failed_login_attempts)->toBe(0)
        ->and(DB::table('company_sessions')->where('user_id', $account->id)->exists())->toBeFalse();
    Mail::assertQueued(PasswordChanged::class);
});

it('sends a first-login account to set its password before anything else', function (string $prefix, string $guard) {
    $account = $prefix === 'admin'
        ? User::factory()->pendingFirstLogin()->withTwoFactor()->create()
        : CompanyUser::factory()->pendingFirstLogin()->create();

    $this->actingAs($account, $guard)
        ->get(route("{$prefix}.dashboard"))
        ->assertRedirect(route("{$prefix}.user-password.edit"));

    $this->actingAs($account, $guard)
        ->get(route("{$prefix}.user-password.edit"))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('mustChange', true)->where('firstLogin', true));
})->with([['admin', 'web'], ['portal', 'company']]);

it('forces a new password after 90 days', function () {
    $account = companyUser(['password_changed_at' => now()->subDays(91)]);

    $this->actingAs($account, 'company')
        ->get(route('portal.dashboard'))
        ->assertRedirect(route('portal.user-password.edit'));
});

it('changes the password, clears first login and ends the other sessions only', function () {
    $account = CompanyUser::factory()->pendingFirstLogin('Temp-Pass-1234')->create();
    DB::table('company_sessions')->insert(['id' => 'other-device', 'user_id' => $account->id, 'payload' => '', 'last_activity' => time()]);
    // A staff session with the same numeric ID must survive (D-048).
    DB::table('sessions')->insert(['id' => 'staff-device', 'user_id' => $account->id, 'payload' => '', 'last_activity' => time()]);

    changePassword($account, 'Temp-Pass-1234', 'Brand-New-Pass1!')
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('portal.dashboard'));

    $account->refresh();
    expect($account->first_login)->toBeFalse()
        ->and($account->temp_password_expires_at)->toBeNull()
        ->and($account->password_changed_at)->not->toBeNull()
        ->and(DB::table('company_sessions')->where('id', 'other-device')->exists())->toBeFalse()
        ->and(DB::table('sessions')->where('id', 'staff-device')->exists())->toBeTrue();
    Mail::assertQueued(PasswordChanged::class, fn ($mail) => $mail->hasTo($account->email));
});

it('requires the current password', function () {
    $account = companyUser();

    changePassword($account, 'wrong-password', 'Brand-New-Pass1!')
        ->assertSessionHasErrors('current_password');
});

it('refuses the last 5 passwords and accepts the 6th', function () {
    $account = companyUser(['password' => 'Password-0000!']);
    $current = 'Password-0000!';

    foreach (range(1, 5) as $n) {
        $next = "Password-000{$n}!";
        $this->travel(1)->minutes(); // past the 6-per-minute throttle
        changePassword($account->fresh(), $current, $next)->assertSessionHasNoErrors();
        $current = $next;
    }

    // Current is 5; 1 to 5 are the last 5 passwords.
    foreach (range(1, 5) as $n) {
        $this->travel(1)->minutes();
        changePassword($account->fresh(), $current, "Password-000{$n}!")->assertSessionHasErrors('password');
    }

    // Password 0 is the 6th most recent: allowed again.
    $this->travel(1)->minutes();
    changePassword($account->fresh(), $current, 'Password-0000!')->assertSessionHasNoErrors();
});

it('refuses the same password in a reset', function () {
    $account = companyUser(['password' => 'Same-Password-1!']);
    $token = Password::broker('company_users')->createToken($account);

    $this->post(route('portal.password.update'), [
        'token' => $token,
        'email' => $account->email,
        'password' => 'Same-Password-1!',
        'password_confirmation' => 'Same-Password-1!',
    ])->assertSessionHasErrors('password');
});

it('sends the expiry reminder 14 days before, once per password', function () {
    $due = companyUser(['password_changed_at' => now()->subDays(77)]);
    $notYet = companyUser(['password_changed_at' => now()->subDays(70)]);
    $staffDue = staffUser(attributes: ['password_changed_at' => now()->subDays(80)]);

    $this->artisan('identity:password-expiry-reminders')->assertSuccessful();
    $this->artisan('identity:password-expiry-reminders')->assertSuccessful();

    Mail::assertQueued(PasswordExpiryReminder::class, 2);
    Mail::assertQueued(PasswordExpiryReminder::class, fn ($mail) => $mail->hasTo($due->email));
    Mail::assertQueued(PasswordExpiryReminder::class, fn ($mail) => $mail->hasTo($staffDue->email));
    Mail::assertNotQueued(PasswordExpiryReminder::class, fn ($mail) => $mail->hasTo($notYet->email));
});

it('schedules the expiry reminder daily', function () {
    $this->artisan('schedule:list')->expectsOutputToContain('identity:password-expiry-reminders');
});
