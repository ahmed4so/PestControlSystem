<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A stage of a workflow template; its code is an application status
 * (02-data-model §4.7, 04 §5.1).
 *
 * @property int $id
 * @property int $workflow_template_id
 * @property string $code
 * @property string $name_en
 * @property string $name_so
 * @property int $sequence
 * @property string $actor_role
 * @property string $company_status
 * @property bool $is_final
 * @property list<string>|null $review_items
 */
class WorkflowStage extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = ['code', 'name_en', 'name_so', 'sequence', 'actor_role', 'company_status', 'is_final', 'review_items'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sequence' => 'integer',
            'is_final' => 'boolean',
            'review_items' => 'array',
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
