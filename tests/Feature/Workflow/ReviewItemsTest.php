<?php

declare(strict_types=1);

use App\Domain\Applications\Actions\RecordReviewItems;
use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Workflow\Enums\ReviewResult;
use App\Domain\Workflow\Exceptions\TransitionRefused;
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

it('keeps a recorded result: no second result, no change, no deletion', function () {
    $registrar = staffWithRole('REGISTRAR');
    $application = advanceTo(submittedApplication(), ApplicationStatus::AdminReview, $registrar);
    $record = app(RecordReviewItems::class);
    $record->handle($application, $registrar, 'ADMIN_REVIEW', ['ITEM_A' => ['result' => 'COMPLIANT']]);

    expect(recordRefusal(fn () => $record->handle($application, $registrar, 'ADMIN_REVIEW', [
        'ITEM_A' => ['result' => 'DEFICIENT', 'comment' => 'Changed my mind.'],
    ])))->toBe(__(RecordReviewItems::REASON_RECORDED, ['item' => 'ITEM_A']));

    $item = ReviewItem::query()->sole();
    expect(fn () => $item->update(['result' => ReviewResult::Deficient]))->toThrow(LogicException::class)
        ->and(fn () => $item->delete())->toThrow(LogicException::class)
        ->and($item->fresh()->result)->toBe(ReviewResult::Compliant);
});
