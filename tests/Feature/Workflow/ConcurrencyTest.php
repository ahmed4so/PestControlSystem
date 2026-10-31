<?php

declare(strict_types=1);

use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Applications\Enums\LicenseCategory;
use App\Domain\Applications\Enums\Service;
use App\Domain\Workflow\Engine\WorkflowEngine;
use App\Domain\Workflow\Enums\WorkflowActionCode;
use App\Domain\Workflow\Exceptions\AlreadyProcessed;
use App\Models\Application;
use App\Models\Company;
use App\Models\WorkflowAction;
use Illuminate\Database\DeadlockException;
use Illuminate\Support\Facades\DB;

/*
 * Row locking (04 §4 rule 1, 04 §16 item 4, D-096). The test database
 * transaction hides rows from other connections, so the application used
 * here is written and committed through a second connection and removed
 * afterwards.
 */

it('waits for another transaction holding the row, then says "already processed"', function () {
    config(['database.connections.mysql_lock' => config('database.connections.'.config('database.default'))]);
    $other = DB::connection('mysql_lock');

    loadTestWorkflow();
    $registrar = staffWithRole('REGISTRAR');

    $companyId = $other->table('companies')->insertGetId([
        ...Company::factory()->make()->getAttributes(),
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $applicationId = $other->table('applications')->insertGetId([
        'company_id' => $companyId,
        'service' => Service::CompanyLicense->value,
        'license_category' => LicenseCategory::Importer->value,
        'application_type' => 'NEW',
        'status' => ApplicationStatus::Submitted->value,
        'open_key' => Service::CompanyLicense->openKey(LicenseCategory::Importer),
        'created_at' => now(), 'updated_at' => now(),
    ]);

    try {
        // What the user's screen holds; the engine reads the row itself.
        $application = (new Application)->newFromBuilder([
            'id' => $applicationId, 'company_id' => $companyId, 'status' => ApplicationStatus::Submitted->value,
        ]);

        // Another user's transaction holds the row and moves it on.
        $other->beginTransaction();
        $other->table('applications')->where('id', $applicationId)->lockForUpdate()->first();
        $other->table('applications')->where('id', $applicationId)->update(['status' => ApplicationStatus::AdminReview->value]);

        // The engine waits on the lock (cut short here) instead of reading the old status.
        DB::statement('SET SESSION innodb_lock_wait_timeout = 1');
        expect(fn () => app(WorkflowEngine::class)->perform(
            $application, WorkflowActionCode::Acknowledge, workflowActor($registrar), ApplicationStatus::Submitted,
        ))->toThrow(DeadlockException::class, 'Lock wait timeout');

        // Once the other transaction commits, the engine sees the new status.
        $other->commit();
        expect(fn () => app(WorkflowEngine::class)->perform(
            $application, WorkflowActionCode::Acknowledge, workflowActor($registrar), ApplicationStatus::Submitted,
        ))->toThrow(AlreadyProcessed::class);

        expect(WorkflowAction::query()->where('application_id', $applicationId)->count())->toBe(0);
    } finally {
        if ($other->transactionLevel() > 0) {
            $other->rollBack();
        }

        DB::statement('SET SESSION innodb_lock_wait_timeout = 50');

        // InnoDB keeps row locks taken inside a savepoint until the outer
        // transaction ends, so end the test transaction before cleaning up.
        $default = DB::connection();
        $levels = $default->transactionLevel();
        $default->rollBack(0);

        $other->table('applications')->where('id', $applicationId)->delete();
        $other->table('companies')->where('id', $companyId)->delete();
        $other->disconnect();

        for ($level = 0; $level < $levels; $level++) {
            $default->beginTransaction();
        }
    }
});
