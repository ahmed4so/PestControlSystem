<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Workflow\Enums\ActorRole;
use App\Domain\Workflow\Enums\WorkflowActionCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * "From stage X, actor Y may perform action Z, which moves to stage W"
 * (02-data-model §4.7, 04 §2, §13). `to_conditions` holds conditional
 * targets: an ordered list of [stage, guard] with a default (04 §13).
 *
 * @property int $id
 * @property int $workflow_template_id
 * @property string $from_stage
 * @property WorkflowActionCode $action
 * @property ActorRole $actor_role
 * @property string|null $to_stage
 * @property list<array{stage: string, guard: string|null}>|null $to_conditions
 * @property bool $requires_comment
 * @property bool $requires_review_items
 * @property bool $requires_password
 * @property list<string> $guards
 * @property list<string> $effects
 * @property int $sequence
 */
class WorkflowTransition extends Model
{
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'from_stage', 'action', 'actor_role', 'to_stage', 'to_conditions',
        'requires_comment', 'requires_review_items', 'requires_password',
        'guards', 'effects', 'sequence',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'action' => WorkflowActionCode::class,
            'actor_role' => ActorRole::class,
            'to_conditions' => 'array',
            'requires_comment' => 'boolean',
            'requires_review_items' => 'boolean',
            'requires_password' => 'boolean',
            'guards' => 'array',
            'effects' => 'array',
            'sequence' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<WorkflowTemplate, $this>
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(WorkflowTemplate::class, 'workflow_template_id');
    }
}
