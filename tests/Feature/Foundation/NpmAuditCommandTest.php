<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

// D-153, D-161: ignored advisories by ID, reviewed monthly.
function npmAuditReport(array $advisoryUrls): string
{
    $vulnerabilities = [];

    foreach ($advisoryUrls as $package => [$url, $severity]) {
        $vulnerabilities[$package] = [
            'name' => $package,
            'severity' => $severity,
            'via' => [['source' => 1, 'name' => $package, 'title' => "Issue in {$package}", 'url' => $url, 'severity' => $severity]],
        ];
    }

    return json_encode(['auditReportVersion' => 2, 'vulnerabilities' => $vulnerabilities]);
}

beforeEach(function () {
    $this->travelTo('2026-11-01 12:00:00');
});

it('passes when every advisory is allowed and in date', function () {
    Process::fake(['*' => Process::result(npmAuditReport([
        'deepmerge' => ['https://github.com/advisories/GHSA-gcqx-w48m-w2h4', 'low'],
    ]), exitCode: 1)]);

    $this->artisan('security:npm-audit')
        ->expectsOutputToContain('Allowed: GHSA-gcqx-w48m-w2h4')
        ->assertSuccessful();

    Process::assertRan(fn ($process) => $process->command === ['npm', 'audit', '--omit=dev', '--json']);
});

it('fails on an advisory that is not allowed, even a low one', function () {
    Process::fake(['*' => Process::result(npmAuditReport([
        'deepmerge' => ['https://github.com/advisories/GHSA-gcqx-w48m-w2h4', 'low'],
        'left-pad' => ['https://github.com/advisories/GHSA-aaaa-bbbb-cccc', 'low'],
    ]), exitCode: 1)]);

    $this->artisan('security:npm-audit')
        ->expectsOutputToContain('Not allowed: GHSA-aaaa-bbbb-cccc')
        ->assertFailed();
});

it('fails when an allow-list entry is past its review date', function () {
    $this->travelTo('2026-11-29 12:00:00');
    Process::fake(['*' => Process::result(npmAuditReport([
        'deepmerge' => ['https://github.com/advisories/GHSA-gcqx-w48m-w2h4', 'low'],
    ]), exitCode: 1)]);

    $this->artisan('security:npm-audit')
        ->expectsOutputToContain('was due for review on 2026-11-28')
        ->assertFailed();
});

it('reports an allowed entry that no longer matches any advisory', function () {
    Process::fake(['*' => Process::result(npmAuditReport([]))]);

    $this->artisan('security:npm-audit')
        ->expectsOutputToContain('GHSA-gcqx-w48m-w2h4 no longer matches')
        ->assertSuccessful();
});

it('fails safely when the npm output is not recognised', function () {
    Process::fake(['*' => Process::result('not json', exitCode: 1)]);

    $this->artisan('security:npm-audit')->assertFailed();
});

it('keeps the composer.json ignore list in sync with config/security.php', function () {
    $composer = json_decode(File::get(base_path('composer.json')), true);

    expect(array_keys($composer['config']['audit']['ignore'] ?? []))
        ->toBe(array_keys(config('security.audit_allowlist.composer')));
});
