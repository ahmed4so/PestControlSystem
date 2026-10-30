<?php

declare(strict_types=1);

use App\Domain\Notifications\Actions\SendNotification;
use App\Domain\Notifications\Enums\NotificationCode;
use App\Domain\Notifications\Enums\NotificationStatus;
use App\Domain\Notifications\Mail\CompanyTempPassword;
use App\Domain\Notifications\Mail\NotificationMail;
use App\Domain\Notifications\Mail\PasswordChanged;
use App\Models\NotificationLog;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/*
| Emails (07 §1, §4; D-125 to D-130, D-167).
*/

it('has one Mailable and two templates for every Phase 1 code', function (NotificationCode $code) {
    $class = $code->mailable();

    expect(class_exists($class))->toBeTrue()
        ->and(is_subclass_of($class, NotificationMail::class))->toBeTrue()
        ->and(view()->exists($code->view()))->toBeTrue()
        ->and(view()->exists($code->textView()))->toBeTrue();
})->with(NotificationCode::cases());

it('queues every email after commit, encrypted, with 3 tries', function () {
    $mail = new PasswordChanged(1, 'en');

    expect($mail)->toBeInstanceOf(ShouldQueue::class)
        ->and($mail)->toBeInstanceOf(ShouldBeEncrypted::class)
        ->and($mail->afterCommit)->toBeTrue()
        ->and($mail->tries)->toBe(3);
});

it('logs a send without the body', function () {
    Mail::fake();
    $account = companyUser();

    app(SendNotification::class)($account, NotificationCode::CompanyTempPassword, ['password' => 'Secret-Temp-12', 'url' => 'https://example.test']);

    $log = NotificationLog::query()->sole();
    expect($log->status)->toBe(NotificationStatus::Queued)
        ->and($log->template)->toBe(NotificationCode::CompanyTempPassword)
        ->and($log->email)->toBe($account->email)
        ->and($log->recipient->is($account))->toBeTrue()
        ->and(json_encode($log->getAttributes()))->not->toContain('Secret-Temp-12');
});

it('sends nothing when the transaction rolls back', function () {
    $account = companyUser();

    try {
        DB::transaction(function () use ($account) {
            app(SendNotification::class)($account, NotificationCode::PasswordChanged, ['url' => 'https://example.test']);

            throw new RuntimeException('Action failed');
        });
    } catch (RuntimeException) {
    }

    expect(app('mailer')->getSymfonyTransport()->messages())->toHaveCount(0)
        ->and(NotificationLog::query()->count())->toBe(0);
});

it('marks the log SENT once the mailer accepts it', function () {
    $account = companyUser();

    app(SendNotification::class)($account, NotificationCode::PasswordChanged, ['url' => 'https://example.test']);

    $log = NotificationLog::query()->sole();
    expect($log->status)->toBe(NotificationStatus::Sent)
        ->and($log->sent_at)->not->toBeNull()
        ->and($log->provider_message_id)->not->toBeNull();
});

it('marks the log FAILED with the error after the last try', function () {
    $log = NotificationLog::factory()->create();

    (new PasswordChanged($log->id, 'en'))->failed(new RuntimeException('Resend unavailable'));

    expect($log->fresh()->status)->toBe(NotificationStatus::Failed)
        ->and($log->fresh()->error)->toBe('Resend unavailable');
});

it('lists failed emails for the System Administrator only', function () {
    NotificationLog::factory()->failed('Mailbox full')->create();

    $this->actingAs(systemAdministrator())
        ->get(route('admin.failed-emails.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('logs.data.0.error', 'Mailbox full'));

    $this->actingAs(staffUser())->get(route('admin.failed-emails.index'))->assertForbidden();
});

it('uses the SARIS subject format in the preferred language', function () {
    $mail = new PasswordChanged(1, 'en', ['name' => 'Amina', 'url' => 'https://example.test'], reference: 'CL-2026-00012');

    $mail->assertHasSubject('SARIS — Your password was changed — CL-2026-00012');
});

it('renders HTML and plain text with the footer and no attachments', function () {
    $mail = new PasswordChanged(1, 'en', ['name' => 'Amina', 'url' => 'https://example.test/login']);

    $mail->assertSeeInHtml('Hello Amina,')
        ->assertSeeInText('Hello Amina,')
        ->assertSeeInHtml('https://example.test/login')
        ->assertSeeInText('info@saris.gov.so')
        ->assertSeeInHtml('+252 612 221 431');

    expect($mail->attachments)->toBe([]);
});

it('shows the preferred language first and the other below (D-125)', function () {
    app('translator')->addLines(['*.Hello :name,' => 'Salaan :name,'], 'so');

    $html = (new PasswordChanged(1, 'so', ['name' => 'Amina', 'url' => 'https://example.test']))->render();

    expect($html)->toContain('Salaan Amina,')
        ->and(strpos($html, 'Salaan Amina,'))->toBeLessThan(strpos($html, 'Hello Amina,'));
});

it('shows a text once while the Somali is still the English placeholder (D-167)', function () {
    $html = (new PasswordChanged(1, 'en', ['name' => 'Amina', 'url' => 'https://example.test']))->render();

    expect(substr_count($html, 'Hello Amina,'))->toBe(1);
});

it('puts the temporary password in the email and nowhere else', function () {
    Mail::fake();
    $account = companyUser();

    app(SendNotification::class)($account, NotificationCode::CompanyTempPassword, ['password' => 'Abc-Temp-9999', 'url' => 'https://example.test']);

    Mail::assertQueued(CompanyTempPassword::class, function ($mail) {
        return str_contains($mail->render(), 'Abc-Temp-9999');
    });
});
