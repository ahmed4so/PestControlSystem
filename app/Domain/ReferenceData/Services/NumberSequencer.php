<?php

declare(strict_types=1);

namespace App\Domain\ReferenceData\Services;

use App\Domain\ReferenceData\Enums\NumberPrefix;
use App\Models\NumberSequence;
use Illuminate\Support\Facades\DB;

/**
 * Hands out the next number for a prefix and year. The counter row is locked
 * with SELECT … FOR UPDATE inside a transaction, so two requests can never get
 * the same number (02-data-model §4.2). Call it inside the transaction that
 * saves the numbered record, so a rollback also gives the number back.
 */
class NumberSequencer
{
    public function next(NumberPrefix $prefix, ?int $year = null): string
    {
        $year ??= $this->currentYear();

        $number = DB::transaction(function () use ($prefix, $year): int {
            NumberSequence::query()->insertOrIgnore([
                'prefix' => $prefix->value,
                'year' => $year,
                'last_number' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $sequence = NumberSequence::query()
                ->where('prefix', $prefix->value)
                ->where('year', $year)
                ->lockForUpdate()
                ->firstOrFail();

            $sequence->increment('last_number');

            return $sequence->last_number;
        });

        return $prefix->format($year, $number);
    }

    /**
     * Numbers follow the calendar year in the display time zone (Africa/Mogadishu).
     */
    private function currentYear(): int
    {
        return (int) now()->setTimezone((string) config('app.display_timezone'))->format('Y');
    }
}
