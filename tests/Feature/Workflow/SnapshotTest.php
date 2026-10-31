<?php

declare(strict_types=1);

use App\Domain\Applications\Snapshots\SnapshotBuilder;
use App\Domain\Applications\Snapshots\SnapshotHasher;
use App\Domain\Workflow\Enums\WorkflowActionCode;
use App\Models\ApplicationVersion;
use App\Models\Company;
use App\Models\Premises;

/*
 * Version snapshots and their hash (02 §6.3, D-042, D-069, D-190).
 */

it('builds the same snapshot and hash from the same data', function () {
    $application = readyApplication();
    $builder = app(SnapshotBuilder::class);
    $hasher = app(SnapshotHasher::class);

    $first = $builder->build($application);
    $second = $builder->build($application->fresh());

    expect($second)->toBe($first)
        ->and($hasher->hash($second))->toBe($hasher->hash($first))
        ->and($hasher->hash($first))->toMatch('/^[0-9a-f]{64}$/');
});

it('changes the hash when one value changes', function () {
    $application = readyApplication();
    $hasher = app(SnapshotHasher::class);
    $before = $hasher->hash(app(SnapshotBuilder::class)->build($application));

    Company::query()->whereKey($application->company_id)->update(['phone' => '+252619999999']);

    expect($hasher->hash(app(SnapshotBuilder::class)->build($application)))->not->toBe($before);
});

it('hashes canonical JSON, whatever the key order', function () {
    $hasher = app(SnapshotHasher::class);

    expect($hasher->hash(['b' => 1, 'a' => ['y' => 2, 'x' => [3, 1]]]))
        ->toBe($hasher->hash(['a' => ['x' => [3, 1], 'y' => 2], 'b' => 1]))
        ->not->toBe($hasher->hash(['a' => ['x' => [1, 3], 'y' => 2], 'b' => 1]));
});

it('copies what the application references, without files, plain national IDs or a checklist', function () {
    $application = readyApplication();
    $premises = Premises::factory()->create(['company_id' => $application->company_id]);
    $application->premises()->attach($premises->id);

    $snapshot = app(SnapshotBuilder::class)->build($application);
    $person = $application->technicalPersons()->withoutGlobalScope('company')->sole();
    $json = json_encode($snapshot);

    expect($snapshot['schema_version'])->toBe(1)
        ->and($snapshot['application']['service'])->toBe('COMPANY_LICENSE')
        ->and($snapshot['company']['address']['fms_name_en'])->not->toBeNull()
        ->and($snapshot['owner']['full_name'])->not->toBeNull()
        ->and($snapshot['technical_persons'])->toHaveCount(1)
        ->and(collect($snapshot['technical_persons'][0]['documents'])->pluck('document_type')->sort()->values()->all())->toBe(['CV', 'DEGREE_CERTIFICATE'])
        ->and(collect($snapshot['documents'])->every(fn ($document) => is_int($document['stored_file_id'])))->toBeTrue()
        ->and($snapshot['premises'][0]['label'])->toBe($premises->label)
        ->and($snapshot['premises'][0])->not->toHaveKey('checklist')
        ->and($json)->not->toContain($person->national_id_no);
});

it('stores a version whose hash verifies after it is read back from the database', function () {
    $application = submittedApplication();
    $version = ApplicationVersion::query()->where('application_id', $application->id)->sole();

    expect(app(SnapshotHasher::class)->verify($version))->toBeTrue();
});

it('keeps an earlier version unchanged when the profile changes later', function () {
    $registrar = staffWithRole('REGISTRAR');
    $application = submittedApplication();
    $v1 = $application->currentVersion();
    Company::query()->whereKey($application->company_id)->update(['name' => 'Renamed Ltd']);

    act($application, WorkflowActionCode::Acknowledge, $registrar);

    expect(ApplicationVersion::query()->findOrFail($v1->id)->snapshot['company']['name'])->not->toBe('Renamed Ltd')
        ->and(app(SnapshotHasher::class)->verify($v1->fresh()))->toBeTrue();
});
