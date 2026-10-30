<?php

declare(strict_types=1);

use App\Domain\Identity\Support\PublicFormMessages;
use App\Domain\Identity\Support\TemporaryPassword;
use App\Domain\Notifications\Mail\CompanyAccountExists;
use App\Domain\Notifications\Mail\CompanyTempPassword;
use App\Models\CompanyUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\TestResponse;

/*
| Company sign-up and temporary passwords (03 §4, 05 §3.1, D-063, D-080).
*/

beforeEach(fn () => Mail::fake());

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function signUpData(array $overrides = []): array
{
    return [
        'full_name' => 'Amina Hassan',
        'email' => 'amina@example.com',
        'phone' => '+252612345678',
        'consent' => '1',
        ...honeypotFields(),
        ...$overrides,
    ];
}

it('shows the sign-up page with honeypot fields', function () {
    $this->get(route('portal.signup'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('portal/auth/signup')
            ->where('honeypot.enabled', true));
});

it('creates the account and emails a temporary password valid for 1 hour', function () {
    $this->post(route('portal.signup.store'), signUpData())
        ->assertRedirect()
        ->assertSessionHas('status', __(PublicFormMessages::SIGN_UP));

    $account = CompanyUser::query()->where('email', 'amina@example.com')->firstOrFail();

    expect($account->full_name)->toBe('Amina Hassan')
        ->and($account->first_login)->toBeTrue()
        ->and($account->company_id)->toBeNull()
        ->and($account->terms_accepted_at)->not->toBeNull()
        ->and($account->email_verified_at)->toBeNull()
        ->and((int) round(now()->diffInMinutes($account->temp_password_expires_at)))->toBe(60);

    Mail::assertQueued(CompanyTempPassword::class, function (CompanyTempPassword $mail) use ($account) {
        $password = $mail->data['password'];

        return $mail->hasTo('amina@example.com')
            && strlen($password) === 12
            && strspn($password, TemporaryPassword::ALPHABET) === 12
            && Hash::check($password, $account->password);
    });
});

it('normalises the email and phone before saving (D-076)', function () {
    $this->post(route('portal.signup.store'), signUpData([
        'full_name' => '  Amina   Hassan ',
        'email' => ' Amina@Example.COM ',
        'phone' => '061 234 5678',
    ]));

    $account = CompanyUser::query()->firstOrFail();

    expect($account->email)->toBe('amina@example.com')
        ->and($account->phone)->toBe('+252612345678')
        ->and($account->full_name)->toBe('Amina Hassan');
});

it('validates the sign-up fields (05 §3.1)', function (array $data, string $field) {
    $this->post(route('portal.signup.store'), signUpData($data))->assertSessionHasErrors($field);

    expect(CompanyUser::query()->count())->toBe(0);
})->with([
    'name too short' => [['full_name' => 'A'], 'full_name'],
    'name with digits' => [['full_name' => 'Amina 123'], 'full_name'],
    'bad email' => [['email' => 'not-an-email'], 'email'],
    'bad phone' => [['phone' => '+12'], 'phone'],
    'no consent' => [['consent' => null], 'consent'],
]);

it('only says "you already have an account" for an activated email', function () {
    $existing = companyUser(['email' => 'amina@example.com']);
    $passwordBefore = $existing->password;

    $this->post(route('portal.signup.store'), signUpData())
        ->assertSessionHas('status', __(PublicFormMessages::SIGN_UP));

    expect($existing->fresh()->password)->toBe($passwordBefore);
    Mail::assertQueued(CompanyAccountExists::class, fn ($mail) => $mail->hasTo('amina@example.com'));
    Mail::assertNotQueued(CompanyTempPassword::class);
});

it('refreshes a never-activated account with a new temporary password', function () {
    $pending = CompanyUser::factory()->pendingFirstLogin('Old-Temp-1234')->create(['email' => 'amina@example.com']);

    $this->post(route('portal.signup.store'), signUpData());

    expect(Hash::check('Old-Temp-1234', $pending->fresh()->password))->toBeFalse()
        ->and(CompanyUser::query()->count())->toBe(1);
    Mail::assertQueued(CompanyTempPassword::class, 1);
});

it('refuses a filled honeypot field silently', function () {
    $data = signUpData();
    $data[config('honeypot.name_field_name').'_abc123'] = 'I am a bot';

    $this->post(route('portal.signup.store'), $data)
        ->assertSessionHas('status', __(PublicFormMessages::SIGN_UP));

    expect(CompanyUser::query()->count())->toBe(0);
    Mail::assertNothingQueued();
});

it('refuses a form sent in under 3 seconds silently', function () {
    $this->post(route('portal.signup.store'), signUpData(honeypotFields(openedSecondsAgo: 1)))
        ->assertSessionHas('status', __(PublicFormMessages::SIGN_UP));

    expect(CompanyUser::query()->count())->toBe(0);
});

it('refuses a form without the honeypot fields', function () {
    $data = signUpData();
    unset($data[config('honeypot.name_field_name').'_abc123'], $data[config('honeypot.valid_from_field_name')]);

    $this->post(route('portal.signup.store'), $data);

    expect(CompanyUser::query()->count())->toBe(0);
});

it('limits sign-up to 3 per hour per IP', function () {
    foreach (range(1, 3) as $i) {
        $this->post(route('portal.signup.store'), signUpData(['email' => "person{$i}@example.com"]))->assertRedirect();
    }

    $this->post(route('portal.signup.store'), signUpData(['email' => 'person4@example.com']))->assertTooManyRequests();
});

/*
| Same answer whatever the email (03 §15 item 9)
*/

/**
 * The parts of a response a visitor can see.
 *
 * @return array{status: int, location: string|null, flash: mixed}
 */
function visibleAnswer(TestResponse $response): array
{
    return [
        'status' => $response->status(),
        'location' => $response->headers->get('Location'),
        'flash' => session('status'),
    ];
}

it('answers sign-up the same for a new, a pending and an activated email', function () {
    companyUser(['email' => 'active@example.com']);
    CompanyUser::factory()->pendingFirstLogin()->create(['email' => 'pending@example.com']);

    $answers = collect(['new@example.com', 'pending@example.com', 'active@example.com'])
        ->map(fn (string $email) => visibleAnswer(
            $this->from(route('portal.signup'))->post(route('portal.signup.store'), signUpData(['email' => $email]))
        ));

    expect($answers->unique()->count())->toBe(1);
});

it('answers the temporary-password form the same for every email', function () {
    companyUser(['email' => 'active@example.com']);
    CompanyUser::factory()->pendingFirstLogin()->create(['email' => 'pending@example.com']);

    $answers = collect(['unknown@example.com', 'pending@example.com', 'active@example.com'])
        ->map(fn (string $email) => visibleAnswer(
            $this->from(route('portal.temporary-password'))->post(route('portal.temporary-password.store'), ['email' => $email, ...honeypotFields()])
        ));

    expect($answers->unique()->count())->toBe(1)
        ->and($answers->first()['flash'])->toBe(__(PublicFormMessages::TEMPORARY_PASSWORD));
});

it('answers forgot-password the same for known and unknown emails, in both portals', function (string $prefix) {
    $prefix === 'portal'
        ? companyUser(['email' => 'known@example.com'])
        : staffUser(attributes: ['email' => 'known@example.com']);

    $answers = collect(['known@example.com', 'unknown@example.com', 'known@example.com'])
        ->map(fn (string $email) => visibleAnswer(
            $this->from(route("{$prefix}.password.request"))->post(route("{$prefix}.password.email"), ['email' => $email, ...honeypotFields()])
        ));

    // The third request is inside the broker's 60-second throttle and must
    // still look the same.
    expect($answers->unique()->count())->toBe(1)
        ->and($answers->first()['flash'])->toBe(__(PublicFormMessages::FORGOT_PASSWORD));
})->with(['admin', 'portal']);

/*
| New temporary password (03 §4, §15 item 6)
*/

it('sends a new temporary password that replaces the previous one', function () {
    $account = CompanyUser::factory()->pendingFirstLogin('Old-Temp-1234')->create();

    $this->post(route('portal.temporary-password.store'), ['email' => $account->email, ...honeypotFields()]);

    $newPassword = null;
    Mail::assertQueued(CompanyTempPassword::class, function (CompanyTempPassword $mail) use (&$newPassword) {
        $newPassword = $mail->data['password'];

        return true;
    });

    $this->post(route('portal.login.store'), ['email' => $account->email, 'password' => 'Old-Temp-1234'])
        ->assertSessionHasErrors('email');
    $this->post(route('portal.login.store'), ['email' => $account->email, 'password' => $newPassword])
        ->assertRedirect(route('portal.dashboard'));
});

it('does not send a temporary password to an account that has logged in', function () {
    $account = companyUser();
    $passwordBefore = $account->password;

    $this->post(route('portal.temporary-password.store'), ['email' => $account->email, ...honeypotFields()]);

    expect($account->fresh()->password)->toBe($passwordBefore);
    Mail::assertNothingQueued();
});
