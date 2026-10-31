<?php

declare(strict_types=1);

namespace App\Domain\Workflow\Definitions;

use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Applications\Enums\Service;
use App\Domain\Workflow\Enums\ActorRole;
use App\Domain\Workflow\Enums\WorkflowActionCode;
use App\Domain\Workflow\Registry\WorkflowRegistry;

/**
 * Checks a workflow definition before it is loaded (04 §13, D-097):
 * known service, stages, actions, roles, guards and effects; no duplicate
 * transition; every stage reachable from DRAFT; every stage able to reach
 * a final stage. Returns every problem, not just the first.
 */
final class DefinitionValidator
{
    public const SAME = 'same';

    public const RESUME = 'resume_stage';

    public function __construct(private readonly WorkflowRegistry $registry) {}

    /**
     * @param  array<string, mixed>  $definition
     * @return list<string>
     */
    public function errors(array $definition): array
    {
        $errors = [];

        foreach (['code', 'service', 'version', 'stages', 'transitions'] as $key) {
            if (! array_key_exists($key, $definition)) {
                $errors[] = "Missing [{$key}].";
            }
        }

        if ($errors !== []) {
            return $errors;
        }

        if (Service::tryFrom((string) $definition['service']) === null) {
            $errors[] = "Unknown service [{$definition['service']}].";
        }

        if (! is_int($definition['version']) || $definition['version'] < 1) {
            $errors[] = 'The version must be a whole number from 1.';
        }

        /** @var list<array<string, mixed>> $stages */
        $stages = (array) $definition['stages'];
        /** @var list<array<string, mixed>> $transitions */
        $transitions = (array) $definition['transitions'];

        $stageCodes = [];
        $finals = [];

        foreach ($stages as $stage) {
            $code = (string) ($stage['code'] ?? '');
            $final = (bool) ($stage['final'] ?? false);

            if (ApplicationStatus::tryFrom($code) === null) {
                $errors[] = "Unknown stage [{$code}].";
            }

            if (in_array($code, $stageCodes, true)) {
                $errors[] = "Stage [{$code}] is declared twice.";
            }

            if (! $final && ActorRole::tryFrom((string) ($stage['actor'] ?? '')) === null) {
                $errors[] = "Stage [{$code}] has an unknown actor [".($stage['actor'] ?? '').'].';
            }

            foreach ((array) ($stage['review_items'] ?? []) as $item) {
                if (! is_string($item) || $item === '') {
                    $errors[] = "Stage [{$code}] has an invalid review item.";
                }
            }

            $stageCodes[] = $code;

            if ($final) {
                $finals[] = $code;
            }
        }

        if (! in_array(ApplicationStatus::Draft->value, $stageCodes, true)) {
            $errors[] = 'The definition has no DRAFT stage.';
        }

        if ($finals === []) {
            $errors[] = 'The definition has no final stage.';
        }

        $edges = [];
        $seen = [];
        $resumeTargets = [];

        foreach ($transitions as $transition) {
            foreach ((array) ($transition['effects'] ?? []) as $spec) {
                [$name, $param] = WorkflowRegistry::split((string) $spec);

                if ($name === 'SetResumeStage' && $param !== null) {
                    $resumeTargets[] = $param;
                }
            }
        }

        foreach ($transitions as $index => $transition) {
            $label = 'Transition '.($index + 1);
            $from = (string) ($transition['from'] ?? '');
            $action = (string) ($transition['action'] ?? '');

            if (! in_array($from, $stageCodes, true)) {
                $errors[] = "{$label}: unknown stage [{$from}].";
            }

            if (in_array($from, $finals, true)) {
                $errors[] = "{$label}: final stage [{$from}] cannot have transitions.";
            }

            if (WorkflowActionCode::tryFrom($action) === null) {
                $errors[] = "{$label}: unknown action [{$action}].";
            }

            if (ActorRole::tryFrom((string) ($transition['actor'] ?? '')) === null) {
                $errors[] = "{$label}: unknown role [".($transition['actor'] ?? '').'].';
            }

            if (isset($seen["{$from}|{$action}"])) {
                $errors[] = "{$label}: [{$from}] already has the action [{$action}].";
            }
            $seen["{$from}|{$action}"] = true;

            foreach ((array) ($transition['guards'] ?? []) as $spec) {
                if (! $this->registry->hasGuard((string) $spec)) {
                    $errors[] = "{$label}: unknown guard [{$spec}].";
                }

                $errors = [...$errors, ...$this->stageParameterErrors($label, (string) $spec, $stageCodes)];
            }

            foreach ((array) ($transition['effects'] ?? []) as $spec) {
                if (! $this->registry->hasEffect((string) $spec)) {
                    $errors[] = "{$label}: unknown effect [{$spec}].";
                }
            }

            $targets = $this->targets($transition['to'] ?? null, $from, $resumeTargets);

            if ($targets === []) {
                $errors[] = "{$label}: missing target stage.";
            }

            foreach ($targets as $target => $guard) {
                if (! in_array($target, $stageCodes, true)) {
                    $errors[] = "{$label}: unknown target stage [{$target}].";
                }

                if ($guard !== null && ! $this->registry->hasGuard($guard)) {
                    $errors[] = "{$label}: unknown guard [{$guard}].";
                }

                $edges[$from][] = $target;
            }
        }

        return [...$errors, ...$this->reachabilityErrors($stageCodes, $finals, $edges)];
    }

    /**
     * Target stages of a `to`, each with its guard (null for plain targets).
     *
     * @param  list<string>  $resumeTargets
     * @return array<string, string|null>
     */
    public function targets(mixed $to, string $from, array $resumeTargets): array
    {
        $resolve = fn (string $stage): array => match ($stage) {
            self::SAME => [$from],
            self::RESUME => $resumeTargets,
            default => [$stage],
        };

        if (is_string($to)) {
            return array_fill_keys($resolve($to), null);
        }

        if (! is_array($to)) {
            return [];
        }

        $targets = [];

        foreach ($to as $stage => $guard) {
            $isDefault = $stage === 'default';

            foreach ($resolve($isDefault ? (string) $guard : (string) $stage) as $target) {
                $targets[$target] = $isDefault ? null : (string) $guard;
            }
        }

        return $targets;
    }

    /**
     * Review-item guards name a declared stage (`ReviewItemsAllCompliant:ADMIN_REVIEW`).
     *
     * @param  list<string>  $stageCodes
     * @return list<string>
     */
    private function stageParameterErrors(string $label, string $spec, array $stageCodes): array
    {
        [$name, $param] = WorkflowRegistry::split($spec);

        if (str_starts_with($name, 'ReviewItems') && ($param === null || ! in_array($param, $stageCodes, true))) {
            return ["{$label}: guard [{$spec}] must name a declared stage."];
        }

        return [];
    }

    /**
     * @param  list<string>  $stageCodes
     * @param  list<string>  $finals
     * @param  array<string, list<string>>  $edges
     * @return list<string>
     */
    private function reachabilityErrors(array $stageCodes, array $finals, array $edges): array
    {
        $errors = [];
        $reachable = $this->reach([ApplicationStatus::Draft->value], $edges);

        foreach ($stageCodes as $code) {
            if (! in_array($code, $reachable, true)) {
                $errors[] = "Stage [{$code}] cannot be reached from DRAFT.";
            }

            if (! in_array($code, $finals, true) && array_intersect($this->reach([$code], $edges), $finals) === []) {
                $errors[] = "Stage [{$code}] can never reach a final stage.";
            }
        }

        return $errors;
    }

    /**
     * @param  list<string>  $start
     * @param  array<string, list<string>>  $edges
     * @return list<string>
     */
    private function reach(array $start, array $edges): array
    {
        $seen = $start;
        $queue = $start;

        while ($queue !== []) {
            $stage = array_shift($queue);

            foreach ($edges[$stage] ?? [] as $next) {
                if (! in_array($next, $seen, true)) {
                    $seen[] = $next;
                    $queue[] = $next;
                }
            }
        }

        return $seen;
    }
}
