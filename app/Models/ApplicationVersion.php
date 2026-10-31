<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Workflow\Concerns\Immutable;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One submission of an application with the snapshot of everything it
 * referenced (02-data-model §4.6, §6.3; D-042, D-069). Never updated.
 *
 * @property int $id
 * @property int $application_id
 * @property int $version_no
 * @property int $submitted_by
 * @property CarbonImmutable $submitted_at
 * @property string $declaration_name
 * @property string $declaration_designation
 * @property string $declaration_ip
 * @property string $declaration_user_agent
 * @property string|null $response_note
 * @property array<string, mixed> $snapshot
 * @property string $snapshot_hash
 */
class ApplicationVersion extends Model
{
    use Immutable;

    public const UPDATED_AT = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'version_no', 'submitted_by', 'submitted_at',
        'declaration_name', 'declaration_designation', 'declaration_ip', 'declaration_user_agent',
        'response_note', 'snapshot', 'snapshot_hash',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'version_no' => 'integer',
            'submitted_at' => 'datetime',
            'snapshot' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Application, $this>
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * @return HasMany<ReviewItem, $this>
     */
    public function reviewItems(): HasMany
    {
        return $this->hasMany(ReviewItem::class);
    }
}
