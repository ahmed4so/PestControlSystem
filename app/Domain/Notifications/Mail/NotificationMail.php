<?php

declare(strict_types=1);

namespace App\Domain\Notifications\Mail;

use App\Domain\Notifications\Enums\NotificationCode;
use App\Domain\Notifications\Enums\NotificationStatus;
use App\Models\NotificationLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Support\Str;
use Throwable;

/**
 * Shared behaviour of every SARIS email (07 §1, §4; D-125, D-129, D-130):
 * queued after the transaction commits, retried 3 times, encrypted on the
 * queue (some carry a temporary password), HTML and plain text, both
 * languages with the preferred one first. A Somali text still equal to the
 * English placeholder is shown once (D-167).
 */
abstract class NotificationMail extends Mailable implements ShouldBeEncrypted, ShouldQueue
{
    use Queueable;

    public const LOG_HEADER = 'X-Saris-Notification';

    public int $tries = 3;

    /**
     * @var list<int>
     */
    public array $backoff = [60, 300];

    /**
     * @param  array<string, string>  $data  values used by the template
     */
    public function __construct(
        public readonly int $notificationLogId,
        public readonly string $preferredLocale,
        public readonly array $data = [],
        public readonly ?string $reference = null,
    ) {
        $this->afterCommit();
    }

    abstract public function code(): NotificationCode;

    public function envelope(): Envelope
    {
        $subject = 'SARIS — '.__($this->code()->subject(), [], $this->preferredLocale);

        if ($this->reference !== null) {
            $subject .= ' — '.$this->reference;
        }

        return new Envelope(subject: $subject);
    }

    public function headers(): Headers
    {
        return new Headers(text: [self::LOG_HEADER => (string) $this->notificationLogId]);
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.layout',
            text: 'mail.layout-text',
            with: [
                'locales' => $this->locales(),
                'sections' => $this->sections($this->code()->view()),
                'textSections' => $this->sections($this->code()->textView()),
                'footers' => $this->sections('mail.footer'),
                'textFooters' => $this->sections('mail.footer-text'),
            ],
        );
    }

    /**
     * The preferred language first, then the other (D-125).
     *
     * @return list<string>
     */
    public function locales(): array
    {
        return $this->preferredLocale === 'so' ? ['so', 'en'] : ['en', 'so'];
    }

    public function failed(Throwable $exception): void
    {
        NotificationLog::query()->whereKey($this->notificationLogId)->update([
            'status' => NotificationStatus::Failed,
            'error' => Str::limit($exception->getMessage(), 1000),
        ]);
    }

    /**
     * @return list<string>
     */
    private function sections(string $view): array
    {
        $rendered = [];

        foreach ($this->locales() as $locale) {
            $rendered[] = trim(view($view, [
                ...$this->data,
                'locale' => $locale,
                'contact' => config('identity.contact'),
            ])->render());
        }

        return array_values(array_unique($rendered));
    }
}
