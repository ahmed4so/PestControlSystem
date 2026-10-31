<?php

declare(strict_types=1);

use App\Domain\Workflow\Definitions\DefinitionLoader;
use App\Domain\Workflow\Exceptions\InvalidWorkflowDefinition;
use App\Models\WorkflowStage;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowTransition;

/*
 * Definitions: validation (04 §13, 04 §16 item 10), loading and versions
 * (D-071, D-097, D-189).
 */

function fixtureDefinition(): array
{
    return require base_path('tests/Fixtures/workflows/test_flow_v1.php');
}

function definitionErrors(array $definition): array
{
    try {
        app(DefinitionLoader::class)->load($definition);
    } catch (InvalidWorkflowDefinition $invalid) {
        return $invalid->errors;
    }

    return [];
}

function workflowTableCounts(): array
{
    return [WorkflowTemplate::query()->count(), WorkflowStage::query()->count(), WorkflowTransition::query()->count()];
}

it('loads the Company License definition, and a second run changes nothing', function () {
    $this->artisan('workflows:load')
        ->expectsOutputToContain('COMPANY_LICENSE_V1 v1 loaded')
        ->assertSuccessful();

    $template = WorkflowTemplate::query()->where('code', 'COMPANY_LICENSE_V1')->sole();
    $counts = workflowTableCounts();

    expect($template->is_active)->toBeTrue()
        ->and($template->stages()->count())->toBe(15)
        ->and($template->transitions()->count())->toBe(24);

    $this->artisan('workflows:load')
        ->expectsOutputToContain('COMPANY_LICENSE_V1 v1 unchanged')
        ->assertSuccessful();

    expect(workflowTableCounts())->toBe($counts)
        ->and($template->fresh()->definition_hash)->toBe($template->definition_hash);
});

it('stores the T12 conditional target and the review items of 04 §5.3', function () {
    $this->artisan('workflows:load')->assertSuccessful();
    $template = WorkflowTemplate::query()->where('code', 'COMPANY_LICENSE_V1')->sole();

    $t12 = $template->transitions()->where('from_stage', 'CONSOLIDATED_REVIEW')->where('action', 'COMPLETE_CONSOLIDATED_REVIEW')->sole();
    expect($t12->to_stage)->toBeNull()
        ->and($t12->to_conditions)->toEqual([
            ['stage' => 'DG_DECISION', 'guard' => 'PaymentCleared'],
            ['stage' => 'DIRECTOR_REVIEW', 'guard' => null],
        ]);

    expect($template->stages()->where('code', 'TECHNICAL_DOC_REVIEW')->sole()->review_items)
        ->toBe(['TECHNICAL_QUALIFICATION', 'TECHNICAL_CV']);

    // D-192: no company checklist guard; D-193: the draft already holds its key.
    $submit = $template->transitions()->where('action', 'SUBMIT')->sole();
    expect($submit->guards)->not->toContain('ChecklistPhotos')
        ->and($submit->effects)->not->toContain('SetOpenKey')
        ->and($submit->requires_password)->toBeTrue();
});

it('refuses a loaded version whose content changed (D-189)', function () {
    loadTestWorkflow();
    $counts = workflowTableCounts();

    $changed = fixtureDefinition();
    $changed['transitions'][1]['comment'] = true;

    expect(definitionErrors($changed)[0])->toContain('already loaded with different content');
    expect(workflowTableCounts())->toBe($counts);
});

it('makes a newer version the active one and keeps the older one for running applications', function () {
    loadTestWorkflow();
    $v2 = fixtureDefinition();
    $v2['version'] = 2;
    app(DefinitionLoader::class)->load($v2);

    expect(WorkflowTemplate::query()->where('code', 'TEST_FLOW_V1')->orderBy('version')->pluck('is_active')->all())
        ->toBe([false, true]);
});

it('refuses an invalid definition with a clear message and loads nothing', function (Closure $break, string $message) {
    $definition = fixtureDefinition();
    $break($definition);

    expect(implode("\n", definitionErrors($definition)))->toContain($message)
        ->and(workflowTableCounts())->toBe([0, 0, 0]);
})->with([
    'unreachable stage' => [function (array &$d) {
        $d['stages'][] = ['code' => 'INSPECTION_REPORT', 'actor' => 'INSPECTOR'];
        $d['transitions'][] = ['from' => 'INSPECTION_REPORT', 'action' => 'SUBMIT_INSPECTION_REPORT', 'actor' => 'INSPECTOR', 'to' => 'CONSOLIDATED_REVIEW'];
    }, 'Stage [INSPECTION_REPORT] cannot be reached from DRAFT.'],
    'dead end' => [function (array &$d) {
        $d['stages'][] = ['code' => 'INSPECTION_REPORT', 'actor' => 'INSPECTOR'];
        $d['transitions'][] = ['from' => 'CONSOLIDATED_REVIEW', 'action' => 'SCHEDULE_INSPECTION', 'actor' => 'REGISTRAR', 'to' => 'INSPECTION_REPORT'];
    }, 'Stage [INSPECTION_REPORT] can never reach a final stage.'],
    'unknown guard' => [function (array &$d) {
        $d['transitions'][1]['guards'] = ['NoSuchGuard'];
    }, 'unknown guard [NoSuchGuard]'],
    'unknown effect' => [function (array &$d) {
        $d['transitions'][1]['effects'] = ['NoSuchEffect'];
    }, 'unknown effect [NoSuchEffect]'],
    'unknown role' => [function (array &$d) {
        $d['transitions'][1]['actor'] = 'JANITOR';
    }, 'unknown role [JANITOR]'],
    'undeclared stage' => [function (array &$d) {
        $d['transitions'][1]['to'] = 'PERMISSION_TO_PAY';
    }, 'unknown target stage [PERMISSION_TO_PAY]'],
    'duplicate transition' => [function (array &$d) {
        $d['transitions'][] = $d['transitions'][1];
    }, '[SUBMITTED] already has the action [ACKNOWLEDGE]'],
    'unknown action' => [function (array &$d) {
        $d['transitions'][1]['action'] = 'WITHDRAW';
    }, 'unknown action [WITHDRAW]'],
    'review items of an undeclared stage' => [function (array &$d) {
        $d['transitions'][2]['guards'] = ['ReviewItemsAllCompliant:INSPECTION_REPORT'];
    }, 'must name a declared stage'],
    'transition out of a final stage' => [function (array &$d) {
        $d['transitions'][] = ['from' => 'ISSUED', 'action' => 'RESUBMIT', 'actor' => 'COMPANY', 'to' => 'DRAFT'];
    }, 'final stage [ISSUED] cannot have transitions'],
    'unknown service' => [function (array &$d) {
        $d['service'] = 'FISHING_PERMIT';
    }, 'Unknown service [FISHING_PERMIT]'],
]);

it('fails the command for an invalid file and loads nothing from it', function () {
    $folder = storage_path('framework/testing/workflows-'.uniqid());
    mkdir($folder, 0777, true);
    $definition = fixtureDefinition();
    $definition['transitions'][1]['guards'] = ['NoSuchGuard'];
    file_put_contents($folder.'/broken_v1.php', '<?php return '.var_export($definition, true).';');

    try {
        $this->artisan('workflows:load', ['--path' => $folder])
            ->expectsOutputToContain('unknown guard [NoSuchGuard]')
            ->assertFailed();
    } finally {
        array_map('unlink', glob($folder.'/*.php') ?: []);
        rmdir($folder);
    }

    expect(workflowTableCounts())->toBe([0, 0, 0]);
});
