<?php

declare(strict_types=1);

use App\Domain\Notifications\Mail\EmailChangeConfirm;
use App\Domain\Notifications\Mail\EmailChangeNotice;
use App\Models\CompanyUser;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\TestResponse;

/*
| Company login-email change (03 §5.1, 05 §3.2, D-093).
*/

beforeEach(fn () => Mail::fake());

function requestEmailChange(CompanyUser $account, string $email): TestResponse
{
    return test()->actingAs($account, 'company')
        ->withSession(passwordConfirmed())
        ->post(route('portal.email.store'), ['email' => $email]);
}

function confirmationToken(): string
{
    $url = null;
    Mail::assertQueued(EmailChangeConfirm::class, function (EmailChangeConfirm $mail) use (&$url) {
        $url = $mail->data['url'];

        return true;
    });

    return basename((string) parse_url((string) $url, PHP_URL_PATH));
}

it('needs a recent password confirmation', function () {
    $this->actingAs(companyUser(), 'company')
        ->get(route('portal.email.edit'))
        ->assertRedirect(route('portal.password.confirm'));
});

it('sends a link to the new email and a notice to the old one, and changes nothing yet', function () {
    $account = companyUser(['email' => 'old@example.com']);

    requestEmailChange($account, ' New@Example.com ')->assertSessionHasNoErrors();

    expect($account->fresh()->email)->toBe('old@example.com')
        ->and($account->fresh()->pending_email)->toBe('new@example.com');
    Mail::assertQueued(EmailChangeConfirm::class, fn ($mail) => $mail->hasTo('new@example.com'));
    Mail::assertQueued(EmailChangeNotice::class, fn ($mail) => $mail->hasTo('old@example.com'));
});

it('changes the email when the link is used, once', function () {
    $account = companyUser(['email' => 'old@example.com']);
    requestEmailChange($account, 'new@example.com');
    $token = confirmationToken();

    $this->get(route('portal.email.confirm', $token))->assertSessionHas('success');
    expect($account->fresh()->email)->toBe('new@example.com')
        ->and($account->fresh()->pending_email)->toBeNull();

    $this->get(route('portal.email.confirm', $token))->assertSessionHas('error');
});

it('refuses an expired link', function () {
    $account = companyUser(['email' => 'old@example.com']);
    requestEmailChange($account, 'new@example.com');
    $token = confirmationToken();

    $this->travel(61)->minutes();

    $this->get(route('portal.email.confirm', $token))->assertSessionHas('error');
    expect($account->fresh()->email)->toBe('old@example.com');
});

it('refuses an email used by another company account', function () {
    companyUser(['email' => 'taken@example.com']);

    requestEmailChange(companyUser(), 'taken@example.com')->assertSessionHasErrors('email');
});

it('refuses the link when the email was taken in the meantime', function () {
    $account = companyUser();
    requestEmailChange($account, 'new@example.com');
    $token = confirmationToken();
    companyUser(['email' => 'new@example.com']);

    $this->get(route('portal.email.confirm', $token))->assertSessionHas('error');
    expect($account->fresh()->email)->not->toBe('new@example.com');
});

it('stores only a hash of the token', function () {
    $account = companyUser();
    requestEmailChange($account, 'new@example.com');
    $token = confirmationToken();

    expect($account->fresh()->pending_email_token_hash)->toBe(hash('sha256', $token))
        ->and($account->fresh()->pending_email_token_hash)->not->toBe($token);
});
