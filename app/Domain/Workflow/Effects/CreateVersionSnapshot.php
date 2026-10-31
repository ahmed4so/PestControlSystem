<?php

declare(strict_types=1);

namespace App\Domain\Workflow\Effects;

use App\Domain\Applications\Snapshots\SnapshotBuilder;
use App\Domain\Applications\Snapshots\SnapshotHasher;
use App\Domain\ReferenceData\Support\FieldFormats;
use App\Domain\Workflow\Contracts\Effect;
use App\Domain\Workflow\Engine\TransitionContext;
use App\Models\ApplicationVersion;
use App\Models\CompanyUser;
use LogicException;

/**
 * A new immutable ApplicationVersion with the snapshot of everything the
 * application references and its hash (04 §12, D-042, D-069, D-190). The
 * declaration is typed by the account holder (02 §4.6).
 */
final class CreateVersionSnapshot implements Effect
{
    public function __construct(
        private readonly SnapshotBuilder $builder,
        private readonly SnapshotHasher $hasher,
    ) {}

    public function apply(TransitionContext $context, ?string $param): void
    {
        $application = $context->application;
        $account = $context->actor->user;

        if (! $account instanceof CompanyUser) {
            throw new LogicException('Only the company submits a version.');
        }

        $snapshot = $this->builder->build($application);

        $version = new ApplicationVersion([
            'version_no' => $application->current_version_no + 1,
            'submitted_by' => $account->id,
            'submitted_at' => now(),
            'declaration_name' => (string) $context->payload('declaration_name'),
            'declaration_designation' => (string) $context->payload('declaration_designation'),
            'declaration_ip' => (string) ($context->actor->ip ?? ''),
            'declaration_user_agent' => (string) ($context->actor->userAgent ?? ''),
            'response_note' => $context->payload('response_note'),
            'snapshot' => $snapshot,
            'snapshot_hash' => $this->hasher->hash($snapshot),
        ]);
        $version->application()->associate($application);
        $version->save();

        $application->current_version_no = $version->version_no;
        $context->version = $version;
    }

    public function payloadRules(?string $param): array
    {
        return [
            'declaration_name' => ['required', ...FieldFormats::name()],
            'declaration_designation' => ['required', 'string', 'min:2', 'max:150'],
            'response_note' => ['nullable', ...FieldFormats::textLong()],
        ];
    }
}
