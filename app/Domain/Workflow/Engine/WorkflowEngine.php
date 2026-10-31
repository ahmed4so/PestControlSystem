<?php

declare(strict_types=1);

namespace App\Domain\Workflow\Engine;

use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Audit\Support\AuditLog;
use App\Domain\ReferenceData\Support\FieldFormats;
use App\Domain\Workflow\Checks\AssignmentCheck;
use App\Domain\Workflow\Checks\DelegationCheck;
use App\Domain\Workflow\Checks\PasswordCheck;
use App\Domain\Workflow\Checks\RealmCheck;
use App\Domain\Workflow\Checks\RoleCheck;
use App\Domain\Workflow\Checks\SegregationCheck;
use App\Domain\Workflow\Contracts\ActorCheck;
use App\Domain\Workflow\Enums\WorkflowActionCode;
use App\Domain\Workflow\Exceptions\AlreadyProcessed;
use App\Domain\Workflow\Exceptions\TransitionRefused;
use App\Domain\Workflow\Registry\WorkflowRegistry;
use App\Models\Application;
use App\Models\CompanyUser;
use App\Models\User;
use App\Models\WorkflowAction;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowTransition;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * The only way an application's status changes (04 §4, D-096). In one
 * transaction:
 *  1. lock the application row;
 *  2. the status must still be the one the user saw, else "already processed";
 *  3. find the transition in the application's template version;
 *  4. actor checks: realm, role, assignment, delegation, segregation (04 §10);
 *  5. password confirmation when required (D-085);
 *  6. payload: comment and the input the effects need;
 *  7. guards, every failure reported;
 *  8. effects; 9. new status; 10. WorkflowAction and audit entry.
 * Any failure rolls everything back. Emails queued by effects are sent only
 * after the commit (D-129).
 */
final class WorkflowEngine
{
    /** Special targets in a definition (04 §5.2: "same", "value of resume_stage"). */
    public const SAME_STAGE = '@same';

    public const RESUME_STAGE = '@resume_stage';

    public const NO_TRANSITION = 'This action is not possible at this stage.';

    public const NO_WORKFLOW = 'No workflow is loaded for this service.';

    public function __construct(private readonly WorkflowRegistry $registry) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function perform(
        Application $application,
        WorkflowActionCode $action,
        WorkflowActor $actor,
        ApplicationStatus|string $expectedStatus,
        array $payload = [],
    ): WorkflowAction {
        $expected = $expectedStatus instanceof ApplicationStatus ? $expectedStatus->value : $expectedStatus;

        return DB::transaction(function () use ($application, $action, $actor, $expected, $payload): WorkflowAction {
            $locked = Application::query()->withoutGlobalScope('company')->whereKey($application->id)->lockForUpdate()->firstOrFail();

            if ($locked->status->value !== $expected) {
                throw new AlreadyProcessed;
            }

            $template = $this->templateFor($locked);
            $transition = $this->transition($template, $locked->status->value, $action)
                ?? throw TransitionRefused::because(self::NO_TRANSITION);

            $context = new TransitionContext($locked, $template, $transition, $actor, $payload, $locked->currentVersion());
            $resumeStage = $locked->resume_stage;

            foreach ($this->actorChecks(withPassword: true) as $check) {
                $reason = $check->check($context);

                if ($reason !== null) {
                    throw TransitionRefused::because($reason);
                }
            }

            $context->payload = $this->validatePayload($context);

            $reasons = [];
            foreach ($transition->guards as $spec) {
                [$guard, $param] = $this->registry->guard($spec);
                array_push($reasons, ...$guard->check($context, $param)->reasons);
            }

            if ($reasons !== []) {
                throw new TransitionRefused(array_values(array_unique($reasons)));
            }

            foreach ($transition->effects as $spec) {
                [$effect, $param] = $this->registry->effect($spec);
                $effect->apply($context, $param);
            }

            $from = $locked->status->value;
            $locked->status = ApplicationStatus::from($this->target($context, $resumeStage));
            $locked->workflow_template_id ??= $template->id;
            $locked->save();

            $record = new WorkflowAction([
                'application_version_id' => $context->version?->id,
                'action' => $action,
                'from_status' => $from,
                'to_status' => $locked->status->value,
                'actor_role' => $transition->actor_role->value,
                'on_behalf_of_id' => $context->onBehalfOf?->id,
                'comment' => $context->payload('comment'),
                'ip_address' => $actor->ip,
            ]);
            $record->application()->associate($locked);
            $record->actor()->associate($actor->user);
            $record->save();

            AuditLog::record('workflow_action', "Workflow action {$action->value}", $locked, $actor->user, [
                'action' => $action->value,
                'from' => $from,
                'to' => $locked->status->value,
                'on_behalf_of_id' => $context->onBehalfOf?->id,
            ]);

            $application->setRawAttributes($locked->getAttributes(), sync: true);

            return $record;
        });
    }

    /**
     * The actions a user may take now, each with the reasons it is blocked
     * (empty when it can be performed). Payload-dependent guards and the
     * password confirmation are left out; nothing is changed (D-191).
     *
     * @return list<array{action: string, to: string|null, requires_comment: bool, requires_password: bool, blocked_by: list<string>}>
     */
    public function availableActions(Application $application, User|CompanyUser $user): array
    {
        $template = $this->templateFor($application, failIfMissing: false);

        if ($template === null) {
            return [];
        }

        $available = [];

        foreach ($template->transitions->where('from_stage', $application->status->value) as $transition) {
            $context = new TransitionContext($application, $template, $transition, new WorkflowActor($user), [], $application->currentVersion());

            foreach ($this->actorChecks(withPassword: false) as $check) {
                if ($check->check($context) !== null) {
                    continue 2;
                }
            }

            $reasons = [];
            foreach ($transition->guards as $spec) {
                [$guard, $param] = $this->registry->guard($spec);

                if (! $guard->needsPayload()) {
                    array_push($reasons, ...$guard->check($context, $param)->reasons);
                }
            }

            $available[] = [
                'action' => $transition->action->value,
                'to' => $transition->to_stage,
                'requires_comment' => $transition->requires_comment,
                'requires_password' => $transition->requires_password,
                'blocked_by' => array_values(array_unique($reasons)),
            ];
        }

        return $available;
    }

    /**
     * @return list<ActorCheck>
     */
    private function actorChecks(bool $withPassword): array
    {
        $checks = [new RealmCheck, new RoleCheck, new AssignmentCheck, new DelegationCheck, new SegregationCheck];

        if ($withPassword) {
            $checks[] = new PasswordCheck;
        }

        return $checks;
    }

    /**
     * The template the application started with, or the active version of
     * its service for a draft (02 §4.6: fixed at first submit).
     */
    private function templateFor(Application $application, bool $failIfMissing = true): ?WorkflowTemplate
    {
        $template = $application->workflow_template_id !== null
            ? WorkflowTemplate::query()->find($application->workflow_template_id)
            : WorkflowTemplate::query()->activeFor($application->service)->first();

        if ($template === null && $failIfMissing) {
            throw TransitionRefused::because(self::NO_WORKFLOW);
        }

        return $template?->load(['stages', 'transitions']);
    }

    private function transition(WorkflowTemplate $template, string $from, WorkflowActionCode $action): ?WorkflowTransition
    {
        return $template->transitions->first(
            fn (WorkflowTransition $transition) => $transition->from_stage === $from && $transition->action === $action,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePayload(TransitionContext $context): array
    {
        $rules = $context->transition->requires_comment
            ? ['comment' => ['required', ...FieldFormats::comment()]]
            : ['comment' => ['nullable', ...FieldFormats::comment()]];

        foreach ($context->transition->effects as $spec) {
            [$effect, $param] = $this->registry->effect($spec);
            $rules = [...$rules, ...$effect->payloadRules($param)];
        }

        $validator = Validator::make($context->payload, $rules);

        if ($validator->fails()) {
            throw new TransitionRefused(array_values($validator->errors()->all()));
        }

        return [...$context->payload, ...$validator->validated()];
    }

    /**
     * A plain target, the stage itself, the stage to resume, or the first
     * conditional target whose guard passes (04 §13). Evaluated after the
     * effects, so a target can depend on what they recorded.
     */
    private function target(TransitionContext $context, ?string $resumeStage): string
    {
        $transition = $context->transition;
        $target = $transition->to_stage;

        foreach ((array) $transition->to_conditions as $condition) {
            if ($condition['guard'] === null) {
                $target = $condition['stage'];
                break;
            }

            [$guard, $param] = $this->registry->guard($condition['guard']);

            if ($guard->check($context, $param)->passed) {
                $target = $condition['stage'];
                break;
            }
        }

        return match ($target) {
            self::SAME_STAGE => $transition->from_stage,
            self::RESUME_STAGE => $resumeStage ?? throw TransitionRefused::because(self::NO_TRANSITION),
            null => throw TransitionRefused::because(self::NO_TRANSITION),
            default => $target,
        };
    }
}
