<?php

declare(strict_types=1);

namespace App\Domain\Workflow\Definitions;

use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Applications\Snapshots\SnapshotHasher;
use App\Domain\Workflow\Engine\WorkflowEngine;
use App\Domain\Workflow\Exceptions\InvalidWorkflowDefinition;
use App\Models\WorkflowTemplate;
use Illuminate\Support\Facades\DB;

/**
 * Loads a validated definition into the workflow tables (D-071, D-097).
 * A loaded version never changes: identical content is skipped, changed
 * content for the same version is refused. The newest version of a
 * service becomes the active one for new applications; running
 * applications keep theirs (D-189).
 */
final class DefinitionLoader
{
    public const LOADED = 'loaded';

    public const UNCHANGED = 'unchanged';

    public function __construct(
        private readonly DefinitionValidator $validator,
        private readonly SnapshotHasher $hasher,
    ) {}

    /**
     * @param  array<string, mixed>  $definition
     */
    public function load(array $definition): string
    {
        $code = (string) ($definition['code'] ?? '?');
        $errors = $this->validator->errors($definition);

        if ($errors !== []) {
            throw new InvalidWorkflowDefinition($code, $errors);
        }

        $hash = $this->hasher->hash($definition);
        $existing = WorkflowTemplate::query()->where('code', $code)->where('version', $definition['version'])->first();

        if ($existing !== null) {
            if (! hash_equals($existing->definition_hash, $hash)) {
                throw new InvalidWorkflowDefinition($code, [
                    "Version {$definition['version']} is already loaded with different content. A change needs a new version file (D-189).",
                ]);
            }

            return self::UNCHANGED;
        }

        DB::transaction(function () use ($definition, $code, $hash): void {
            WorkflowTemplate::query()
                ->where('service', $definition['service'])
                ->where('version', '<', $definition['version'])
                ->update(['is_active' => false]);

            $isNewest = ! WorkflowTemplate::query()
                ->where('service', $definition['service'])
                ->where('version', '>', $definition['version'])
                ->exists();

            $template = WorkflowTemplate::query()->create([
                'code' => $code,
                'service' => $definition['service'],
                'version' => $definition['version'],
                'is_active' => $isNewest,
                'definition_hash' => $hash,
            ]);

            foreach (array_values((array) $definition['stages']) as $sequence => $stage) {
                $status = ApplicationStatus::from((string) $stage['code']);
                $template->stages()->create([
                    'code' => $status->value,
                    'name_en' => (string) ($stage['name_en'] ?? $stage['code']),
                    'name_so' => (string) ($stage['name_so'] ?? $stage['name_en'] ?? $stage['code']),
                    'sequence' => $sequence + 1,
                    'actor_role' => (string) ($stage['actor'] ?? 'NONE'),
                    'company_status' => $status->companyLabel(),
                    'is_final' => (bool) ($stage['final'] ?? false),
                    'review_items' => $stage['review_items'] ?? null,
                ]);
            }

            foreach (array_values((array) $definition['transitions']) as $sequence => $transition) {
                [$toStage, $conditions] = $this->target($transition['to']);

                $template->transitions()->create([
                    'from_stage' => $transition['from'],
                    'action' => $transition['action'],
                    'actor_role' => $transition['actor'],
                    'to_stage' => $toStage,
                    'to_conditions' => $conditions,
                    'requires_comment' => (bool) ($transition['comment'] ?? false),
                    'requires_review_items' => (bool) ($transition['review_items'] ?? false),
                    'requires_password' => (bool) ($transition['password'] ?? false),
                    'guards' => array_values((array) ($transition['guards'] ?? [])),
                    'effects' => array_values((array) ($transition['effects'] ?? [])),
                    'sequence' => $sequence + 1,
                ]);
            }
        });

        return self::LOADED;
    }

    /**
     * `to` as stored: a plain stage (or the "same" / "resume_stage" tokens),
     * or conditional targets in order with a default (04 §13).
     *
     * @return array{0: string|null, 1: list<array{stage: string, guard: string|null}>|null}
     */
    private function target(mixed $to): array
    {
        $token = fn (string $stage): string => match ($stage) {
            DefinitionValidator::SAME => WorkflowEngine::SAME_STAGE,
            DefinitionValidator::RESUME => WorkflowEngine::RESUME_STAGE,
            default => $stage,
        };

        if (is_string($to)) {
            return [$token($to), null];
        }

        $conditions = [];
        $default = null;

        foreach ((array) $to as $stage => $guard) {
            if ($stage === 'default') {
                $default = ['stage' => $token((string) $guard), 'guard' => null];
            } else {
                $conditions[] = ['stage' => $token((string) $stage), 'guard' => (string) $guard];
            }
        }

        if ($default !== null) {
            $conditions[] = $default;
        }

        return [null, $conditions];
    }
}
