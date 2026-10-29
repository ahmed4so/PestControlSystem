<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Process;

/**
 * Runs `npm audit` for production dependencies and fails on any advisory,
 * of any severity, that is not in the allow-list, and on any allow-list
 * entry (npm or Composer) past its review date (D-153, D-161).
 */
class NpmAuditCommand extends Command
{
    protected $signature = 'security:npm-audit';

    protected $description = 'npm audit with the reviewed allow-list (D-153)';

    public function handle(): int
    {
        $failed = false;

        foreach ($this->expiredEntries() as [$source, $id, $reviewBy]) {
            $this->error("Allow-list entry {$id} ({$source}) was due for review on {$reviewBy}. Re-check it, then move review_by or remove it.");
            $failed = true;
        }

        $advisories = $this->advisories();

        if ($advisories === null) {
            $this->error('npm audit returned output this command does not recognise.');

            return self::FAILURE;
        }

        /** @var array<string, array<string, string>> $allowed */
        $allowed = config('security.audit_allowlist.npm', []);

        foreach ($advisories as $id => $advisory) {
            if (array_key_exists($id, $allowed)) {
                $this->line("Allowed: {$id} ({$advisory['package']}, {$advisory['severity']})");

                continue;
            }

            $this->error("Not allowed: {$id} ({$advisory['package']}, {$advisory['severity']}) {$advisory['title']}");
            $failed = true;
        }

        foreach (array_diff(array_keys($allowed), array_keys($advisories)) as $id) {
            $this->warn("Allow-list entry {$id} no longer matches any advisory; remove it from config/security.php.");
        }

        if (! $failed) {
            $this->info('npm audit passed.');
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @return array<string, array{package: string, severity: string, title: string}>|null
     */
    private function advisories(): ?array
    {
        $result = Process::path(base_path())->run(['npm', 'audit', '--omit=dev', '--json']);
        $report = json_decode($result->output(), true);

        if (! is_array($report) || ! is_array($report['vulnerabilities'] ?? null)) {
            return null;
        }

        $advisories = [];

        foreach ($report['vulnerabilities'] as $name => $vulnerability) {
            foreach ((array) ($vulnerability['via'] ?? []) as $via) {
                if (! is_array($via) || ! isset($via['url'])) {
                    continue;
                }

                $advisories[basename((string) $via['url'])] = [
                    'package' => (string) ($via['name'] ?? $name),
                    'severity' => (string) ($via['severity'] ?? $vulnerability['severity'] ?? 'unknown'),
                    'title' => (string) ($via['title'] ?? ''),
                ];
            }
        }

        return $advisories;
    }

    /**
     * @return list<array{0: string, 1: string, 2: string}>
     */
    private function expiredEntries(): array
    {
        $today = Carbon::now((string) config('app.display_timezone'))->toDateString();
        $expired = [];

        foreach ((array) config('security.audit_allowlist', []) as $source => $entries) {
            foreach ((array) $entries as $id => $entry) {
                if ((string) $entry['review_by'] < $today) {
                    $expired[] = [(string) $source, (string) $id, (string) $entry['review_by']];
                }
            }
        }

        return $expired;
    }
}
