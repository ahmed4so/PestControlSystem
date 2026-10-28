<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;

// D-050: MySQL 8 in all environments, test database saris_portal_testing.
it('runs the tests on MySQL 8 using the saris_portal_testing database', function () {
    expect(DB::connection()->getDriverName())->toBe('mysql')
        ->and(DB::connection()->getDatabaseName())->toBe('saris_portal_testing')
        ->and(DB::selectOne('select version() as v')->v)->toStartWith('8.');
});
