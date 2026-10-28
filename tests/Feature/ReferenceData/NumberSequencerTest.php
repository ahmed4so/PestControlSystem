<?php

declare(strict_types=1);

use App\Domain\ReferenceData\Enums\NumberPrefix;
use App\Domain\ReferenceData\Services\NumberSequencer;
use Illuminate\Support\Facades\DB;

it('formats license numbers as PREFIX-YYYY-NNNN (D-011)', function () {
    $sequencer = app(NumberSequencer::class);

    expect($sequencer->next(NumberPrefix::ImportationLicense, 2026))->toBe('PCPIL-2026-0001')
        ->and($sequencer->next(NumberPrefix::ImportationLicense, 2026))->toBe('PCPIL-2026-0002')
        ->and($sequencer->next(NumberPrefix::OperatorLicense, 2026))->toBe('PCA-2026-0001');
});

it('formats application references with five digits and letters with four (D-075)', function () {
    $sequencer = app(NumberSequencer::class);

    expect($sequencer->next(NumberPrefix::CompanyLicenseApplication, 2026))->toBe('CL-2026-00001')
        ->and($sequencer->next(NumberPrefix::ImportPermitApplication, 2026))->toBe('IP-2026-00001')
        ->and($sequencer->next(NumberPrefix::PermissionToPayLetter, 2026))->toBe('PTP-2026-0001')
        ->and($sequencer->next(NumberPrefix::AccountRecoveryRequest, 2026))->toBe('AR-2026-0001');
});

it('restarts each prefix every year', function () {
    $sequencer = app(NumberSequencer::class);

    $sequencer->next(NumberPrefix::RetailerLicense, 2026);
    $sequencer->next(NumberPrefix::RetailerLicense, 2026);

    expect($sequencer->next(NumberPrefix::RetailerLicense, 2027))->toBe('PCPRT-2027-0001');
});

it('uses the current year in Africa/Mogadishu by default', function () {
    // 31 Dec 2026 22:30 UTC is already 1 Jan 2027 in Mogadishu (UTC+3).
    $this->travelTo(now()->setTimezone('UTC')->setDate(2026, 12, 31)->setTime(22, 30));

    expect(app(NumberSequencer::class)->next(NumberPrefix::ImportPermit))->toBe('PCPIP-2027-0001');
});

it('locks the counter row while incrementing', function () {
    DB::enableQueryLog();

    app(NumberSequencer::class)->next(NumberPrefix::WholesaleLicense, 2026);

    expect(collect(DB::getQueryLog())->pluck('query')->contains(fn (string $sql) => str_contains($sql, 'for update')))->toBeTrue();
});

it('gives the number back when the surrounding transaction rolls back', function () {
    $sequencer = app(NumberSequencer::class);

    try {
        DB::transaction(function () use ($sequencer) {
            $sequencer->next(NumberPrefix::ImportationLicense, 2026);

            throw new RuntimeException('save failed');
        });
    } catch (RuntimeException) {
    }

    expect($sequencer->next(NumberPrefix::ImportationLicense, 2026))->toBe('PCPIL-2026-0001');
});
