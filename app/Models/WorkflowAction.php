<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Workflow\Concerns\Immutable;
use App\Domain\Workflow\Enums\WorkflowActionCode;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * The immutable record that a transition happened (02-data-model §4.7,
 * D-071). Written only by the workflow engine.
 *
 * @property int $id
 * @property int $application_id
 * @property int|null $application_version_id
 * @property WorkflowActionCode $action
 * @property string $from_status
 * @property string $to_status
 * @property string $actor_type
 * @property int $actor_id
 * @property string|null $actor_role
 * @property int|null $on_behalf_of_id
 * @property string|null $comment
 * @property string|null $ip_address
 * @property CarbonImmutable $created_at
 */
class WorkflowAction extends Model
{
    use Immutable;

    public const UPDATED_AT = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'application_version_id', 'action', 'from_status', 'to_status',
        'actor_role', 'on_behalf_of_id', 'comment', 'ip_address',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['action' => WorkflowActionCode::class];
    }

    /**
     * @return BelongsTo<Application, $this>
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function actor(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function onBehalfOf(): BelongsTo
    {
        return $this->belongsTo(User::class, 'on_behalf_of_id');
    }
}
