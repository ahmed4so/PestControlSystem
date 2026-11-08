<?php

declare(strict_types=1);

use App\Domain\Notifications\Mail\ApplicationAcknowledged;
use App\Domain\Notifications\Mail\ApplicationResubmitted;
use App\Domain\Notifications\Mail\ApplicationReturned;
use App\Domain\Notifications\Mail\ApplicationSubmitted;
use App\Domain\Notifications\Mail\ReviewerAssigned;
use App\Domain\Notifications\Mail\TechnicalReviewDone;
use App\Domain\Notifications\Mail\WorkItemReady;

/*
 * The workflow emails of 07 §3.3 render in both languages, HTML and text,
 * with the subject format of 07 §4 (D-125, D-130).
 */

function workflowEmailData(): array
{
    return [
        'name' => 'Amina Hassan',
        'reference' => 'CL-2026-00001',
        'service' => 'Pest Control Products Company License',
        'category' => 'Importer',
        'company' => 'Hodan Agro Ltd',
        'url' => 'https://portal.example/portal/applications/1',
        'stage_en' => 'Administrative review',
        'stage_so' => 'Dib-u-eegis maamul',
        'version' => '2',
        'comment' => 'Please upload a readable copy.',
        'items' => [['label' => 'Commerce registration', 'result' => 'Deficient', 'comment' => 'Not readable.']],
    ];
}

it('renders each workflow email in both languages with its reference in the subject', function (string $mailable, string $expected) {
    foreach (['en', 'so'] as $locale) {
        $mail = new $mailable(1, $locale, workflowEmailData(), 'CL-2026-00001');

        $html = $mail->render();
        expect($html)->toContain($expected)
            ->and($html)->toContain('https://portal.example/portal/applications/1')
            ->and($mail->envelope()->subject)->toEndWith('CL-2026-00001');
    }
})->with([
    'submitted' => [ApplicationSubmitted::class, 'CL-2026-00001'],
    'work item' => [WorkItemReady::class, 'Hodan Agro Ltd'],
    'acknowledged' => [ApplicationAcknowledged::class, 'under review'],
    'returned' => [ApplicationReturned::class, 'Not readable.'],
    'resubmitted' => [ApplicationResubmitted::class, 'version 2'],
    'reviewer assigned' => [ReviewerAssigned::class, 'technical review'],
    'review done' => [TechnicalReviewDone::class, 'completed'],
]);
