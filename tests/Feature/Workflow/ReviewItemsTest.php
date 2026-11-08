<?php

declare(strict_types=1);

use App\Domain\Applications\Actions\RecordReviewItems;
use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Workflow\Enums\ReviewResult;
use App\Domain\Workflow\Exceptions\TransitionRefused;
use App\Models\AuditActivity;
use App\Models\ReviewItem;

/*
 * Recording review items (04 §5.3, 02 §4.7, D-024, D-102).
 */

function recordRefusal(Closure $record): ?string
{
    try {
        $record();
    } catch (TransitionRefused $refused) {
        return $refused->reasons[0];
    }

    return null;
}

it('records results on the current version for the current stage', function () {
    $registrar = staffWithRole('REGISTRAR');
    $application = advanceTo(submittedApplication(), ApplicationStatus::AdminReview, $registrar);

    app(RecordReviewItems::class)->handle($application, $registrar, 'ADMIN_REVIEW', [
        'ITEM_A' => ['result' => ReviewResult::Compliant],
        'ITEM_B' => ['result' => 'CLARIFICATION_REQUIRED', 'comment' => 'Which branch is this?'],
    ]);

    $items = ReviewItem::query()->orderBy('item_code')->get();
    expect($items)->toHaveCount(2)
        ->and($items->pluck('application_version_id')->unique()->all())->toBe([$application->currentVersion()->id])
        ->and($items[1]->result)->toBe(ReviewResult::ClarificationRequired)
        ->and($items[1]->reviewed_by)->toBe($registrar->id);
});

it('refuses a result without a comment unless it is compliant', function () {
    $registrar = staffWithRole('REGISTRAR');
    $application = advanceTo(submittedApplication(), ApplicationStatus::AdminReview, $registrar);

    expect(recordRefusal(fn () => app(RecordReviewItems::class)->handle($application, $registrar, 'ADMIN_REVIEW', [
        'ITEM_A' => ['result' => 'DEFICIENT'],
    ])))->toBe(RecordReviewItems::REASON_COMMENT);
    expect(ReviewItem::query()->count())->toBe(0);
});

it('refuses another stage, an unknown item and another role', function () {
    $registrar = staffWithRole('REGISTRAR');
    $application = advanceTo(submittedApplication(), ApplicationStatus::AdminReview, $registrar);
    $record = app(RecordReviewItems::class);

    expect(recordRefusal(fn () => $record->handle($application, $registrar, 'TECHNICAL_DOC_REVIEW', ['ITEM_T' => ['result' => 'COMPLIANT']])))
        ->toBe(RecordReviewItems::REASON_STAGE)
        ->and(recordRefusal(fn () => $record->handle($application, $registrar, 'ADMIN_REVIEW', ['ITEM_Z' => ['result' => 'COMPLIANT']])))
        ->toBe(__(RecordReviewItems::REASON_ITEM, ['item' => 'ITEM_Z']))
        ->and(recordRefusal(fn () => $record->handle($application, staffWithRole('PAYMENT_OFFICER'), 'ADMIN_REVIEW', ['ITEM_A' => ['result' => 'COMPLIANT']])))
        ->toBe(RecordReviewItems::REASON_ROLE)
        ->and(recordRefusal(fn () => $record->handle($application, systemAdministrator(), 'ADMIN_REVIEW', ['ITEM_A' => ['result' => 'COMPLIANT']])))
        ->toBe(RecordReviewItems::REASON_ROLE);

    expect(ReviewItem::query()->count())->toBe(0);
});

it('lets the reviewer correct their own result while at the stage, keeping the old value in the audit log (D-195)', function () {
    $registrar = staffWithRole('REGISTRAR');
    $application = advanceTo(submittedApplication(), ApplicationStatus::AdminReview, $registrar);
    $record = app(RecordReviewItems::class);
    $record->handle($application, $registrar, 'ADMIN_REVIEW', ['ITEM_A' => ['result' => 'COMPLIANT']]);

    $record->handle($application, $registrar, 'ADMIN_REVIEW', [
        'ITEM_A' => ['result' => 'DEFICIENT', 'comment' => 'The stamp is missing.'],
    ]);

    $item = ReviewItem::query()->sole();
    expect($item->result)->toBe(ReviewResult::Deficient)
        ->and($item->comment)->toBe('The stamp is missing.');

    $change = AuditActivity::query()
        ->where('subject_type', $item->getMorphClass())->where('subject_id', $item->id)->where('event', 'updated')->sole();
    expect($change->properties['old']['result'])->toBe('COMPLIANT')
        ->and($change->properties['attributes']['result'])->toBe('DEFICIENT');
});

it('refuses a correction by another reviewer', function () {
    $registrar = staffWithRole('REGISTRAR');
    $application = advanceTo(submittedApplication(), ApplicationStatus::AdminReview, $registrar);
    $record = app(RecordReviewItems::class);
    $record->handle($application, $registrar, 'ADMIN_REVIEW', ['ITEM_A' => ['result' => 'COMPLIANT']]);

    expect(recordRefusal(fn () => $record->handle($application, staffWithRole('REGISTRAR'), 'ADMIN_REVIEW', [
        'ITEM_A' => ['result' => 'DEFICIENT', 'comment' => 'No.'],
    ])))->toBe(__(RecordReviewItems::REASON_OTHER_REVIEWER, ['item' => 'ITEM_A']));
    expect(ReviewItem::query()->sole()->result)->toBe(ReviewResult::Compliant);
});

it('refuses corrections once the application has left the stage, and never deletes a result', function () {
    $registrar = staffWithRole('REGISTRAR');
    $application = advanceTo(submittedApplication(), ApplicationStatus::TechnicalDocReview, $registrar);
    $record = app(RecordReviewItems::class);

    expect(recordRefusal(fn () => $record->handle($application, $registrar, 'ADMIN_REVIEW', [
        'ITEM_A' => ['result' => 'DEFICIENT', 'comment' => 'Too late.'],
    ])))->toBe(RecordReviewItems::REASON_STAGE);

    $item = ReviewItem::query()->where('item_code', 'ITEM_A')->sole();
    expect(fn () => $item->delete())->toThrow(LogicException::class)
        ->and($item->fresh()->result)->toBe(ReviewResult::Compliant);
});
