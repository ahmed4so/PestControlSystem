<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Audit\Concerns\Audited;
use App\Domain\Workflow\Enums\ReviewResult;
use Carbon\CarbonImmutable;
use Database\Factories\ReviewItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * A reviewer's result for one checklist item of a stage, on one version
 * (02-data-model §4.7, 04 §5.3, D-102).
 *
 * @property int $id
 * @property int $application_version_id
 * @property string $stage
 * @property string $item_code
 * @property ReviewResult $result
 * @property string|null $comment
 * @property int $reviewed_by
 * @property CarbonImmutable $reviewed_at
 */
class ReviewItem extends Model
{
    /** @use HasFactory<ReviewItemFactory> */
    use Audited, HasFactory;

    /**
     * Results may be corrected by their reviewer while the application is
     * at the stage (D-195, audited); they are never deleted.
     */
    protected static function booted(): void
    {
        static::deleting(fn () => throw new LogicException('ReviewItem records cannot be deleted.'));
    }

    /**
     * @var list<string>
     */
    protected $fillable = ['stage', 'item_code', 'result', 'comment', 'reviewed_by', 'reviewed_at'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'result' => ReviewResult::class,
            'reviewed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<ApplicationVersion, $this>
     */
    public function version(): BelongsTo
    {
        return $this->belongsTo(ApplicationVersion::class, 'application_version_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
