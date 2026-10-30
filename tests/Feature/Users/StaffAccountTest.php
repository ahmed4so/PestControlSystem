<?php

declare(strict_types=1);

use App\Domain\Identity\Enums\AccountStatus;
use App\Domain\Identity\Enums\StaffRole;
use App\Domain\Notifications\Mail\StaffTempPassword;
use App\Models\AuditActivity;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

/*
| Staff accounts (03 §5.2, 05 §3.3; D-081, D-083, D-086).
*/

beforeEach(function () {
    Mail::fake();
    $this->admin = systemAdministrator();
});

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function staffData(array $overrides = []): array
{
    return [
        'name' => 'Faadumo Ali',
        'email' => 'faadumo@saris.gov.so',
        'phone' => '0612345678',
        'preferred_locale' => 'so',
        'roles' => [StaffRole::PesticideRegistrar->value],
        ...$overrides,
    ];
}

it('creates a staff user with an emailed temporary password valid for 24 hours', function () {
    $this->actingAs($this->admin)
        ->withSession(passwordConfirmed())
        ->post(route('admin.users.store'), staffData())
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.users.index'));

    $staff = User::query()->where('email', 'faadumo@saris.gov.so')->firstOrFail();

    expect($staff->first_login)->toBeTrue()
        ->and($staff->status)->toBe(AccountStatus::Active)
        ->and($staff->phone)->toBe('+252612345678')
        ->and($staff->preferred_locale)->toBe('so')
        ->and($staff->hasRole(StaffRole::PesticideRegistrar->value))->toBeTrue()
        ->and((int) round(now()->diffInHours($staff->temp_password_expires_at)))->toBe(24);

    Mail::assertQueued(StaffTempPassword::class, fn (StaffTempPassword $mail) => $mail->hasTo('faadumo@saris.gov.so')
        && $mail->preferredLocale === 'so'
        && Hash::check($mail->data['password'], $staff->password));
});

it('ignores a password sent with the form', function () {
    $this->actingAs($this->admin)
        ->withSession(passwordConfirmed())
        ->post(route('admin.users.store'), staffData([
            'password' => 'Chosen-By-Admin-1!',
            'password_confirmation' => 'Chosen-By-Admin-1!',
        ]));

    $staff = User::query()->where('email', 'faadumo@saris.gov.so')->firstOrFail();

    expect(Hash::check('Chosen-By-Admin-1!', $staff->password))->toBeFalse();
});

it('validates the staff fields (05 §3.3)', function (array $data, string $field) {
    $this->actingAs($this->admin)
        ->withSession(passwordConfirmed())
        ->post(route('admin.users.store'), staffData($data))
        ->assertSessionHasErrors($field);
})->with([
    'no roles' => [['roles' => []], 'roles'],
    'unknown role' => [['roles' => ['Super Admin']], 'roles.0'],
    'admin with a workflow role' => [['roles' => [StaffRole::SystemAdministrator->value, StaffRole::Inspector->value]], 'roles'],
    'bad language' => [['preferred_locale' => 'fr'], 'preferred_locale'],
    'bad name' => [['name' => 'X'], 'name'],
]);

it('refuses an email already used by staff', function () {
    staffUser(attributes: ['email' => 'faadumo@saris.gov.so']);

    $this->actingAs($this->admin)
        ->withSession(passwordConfirmed())
        ->post(route('admin.users.store'), staffData(['email' => 'Faadumo@SARIS.gov.so']))
        ->assertSessionHasErrors('email');
});

it('needs a recent password confirmation to create staff (D-085)', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.users.create'))
        ->assertRedirect(route('admin.password.confirm'));
});

it('needs the create user permission', function () {
    $this->actingAs(staffUser())
        ->withSession(passwordConfirmed())
        ->post(route('admin.users.store'), staffData())
        ->assertForbidden();
});

it('lets a new staff member log in with the temporary password and must change it, then set up 2FA', function () {
    $this->actingAs($this->admin)->withSession(passwordConfirmed())->post(route('admin.users.store'), staffData());
    auth('web')->logout();
    $this->flushSession();

    $password = null;
    Mail::assertQueued(StaffTempPassword::class, function ($mail) use (&$password) {
        $password = $mail->data['password'];

        return true;
    });

    $this->post(route('admin.login.store'), ['email' => 'faadumo@saris.gov.so', 'password' => $password])
        ->assertRedirect(route('admin.dashboard'));
    $this->get(route('admin.dashboard'))->assertRedirect(route('admin.user-password.edit'));

    $this->put(route('admin.user-password.update'), [
        'current_password' => $password,
        'password' => 'My-Own-Password-1!',
        'password_confirmation' => 'My-Own-Password-1!',
    ])->assertRedirect(route('admin.dashboard'));

    $this->get(route('admin.dashboard'))->assertRedirect(route('admin.two-factor.show'));
});

it('re-sends a temporary password only before the first login', function () {
    $pending = User::factory()->pendingFirstLogin('Old-Temp-1234')->create();
    $active = staffUser();

    $this->actingAs($this->admin)->withSession(passwordConfirmed())
        ->post(route('admin.users.temporary-password', $pending))
        ->assertSessionHas('success');
    $this->actingAs($this->admin)->withSession(passwordConfirmed())
        ->post(route('admin.users.temporary-password', $active))
        ->assertSessionHas('error');

    expect(Hash::check('Old-Temp-1234', $pending->fresh()->password))->toBeFalse();
    Mail::assertQueued(StaffTempPassword::class, 1);
});

it('lets the System Administrator unlock a locked staff account', function () {
    $staff = staffUser(attributes: ['locked_until' => now()->addMinutes(10), 'failed_login_attempts' => 0]);

    $this->actingAs($this->admin)
        ->withSession(passwordConfirmed())
        ->post(route('admin.users.unlock', $staff))
        ->assertSessionHas('success');

    expect($staff->fresh()->locked_until)->toBeNull()
        ->and(AuditActivity::query()->where('event', 'account_unlocked')->exists())->toBeTrue();
});

it('needs the unlock account permission', function () {
    $staff = staffUser(attributes: ['locked_until' => now()->addMinutes(10)]);

    $this->actingAs(staffUser())
        ->withSession(passwordConfirmed())
        ->post(route('admin.users.unlock', $staff))
        ->assertForbidden();
});

it('updates staff details without touching the password', function () {
    $staff = staffUser();
    $passwordBefore = $staff->password;

    $this->actingAs($this->admin)
        ->withSession(passwordConfirmed())
        ->put(route('admin.users.update', $staff), [
            'name' => 'New Name',
            'email' => $staff->email,
            'phone' => '',
            'preferred_locale' => 'en',
            'password' => 'Ignored-Pass-1!',
        ])->assertSessionHasNoErrors();

    expect($staff->fresh()->name)->toBe('New Name')
        ->and($staff->fresh()->password)->toBe($passwordBefore);
});

it('shows the user pages to the System Administrator', function () {
    $staff = staffUser();

    foreach ([route('admin.users.index'), route('admin.users.show', $staff), route('admin.users.edit', $staff), route('admin.roles.index'), route('admin.failed-emails.index')] as $url) {
        $this->actingAs($this->admin)->withSession(passwordConfirmed())->get($url)->assertOk();
    }
});
