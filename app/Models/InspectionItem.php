<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Audit\Concerns\Audited;
use App\Domain\Inspection\Enums\InspectionItemResult;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use LogicException;

/**
 * One checklist answer of an inspection (02 §4.8, 05 §6). Locked once the
 * inspection is signed.
 *
 * @property int $id
 * @property int $inspection_id
 * @property string $item_code
 * @property InspectionItemResult $result
 * @property string|null $note
 */
class InspectionItem extends Model
{
    use Audited, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = ['item_code', 'result', 'note'];

    protected static function booted(): void
    {
        $locked = function (InspectionItem $item): void {
            if (Inspection::query()->whereKey($item->inspection_id)->whereNotNull('signed_at')->exists()) {
                throw new LogicException('A signed inspection report cannot be changed.');
            }
        };

        static::saving($locked);
        static::deleting(fn () => throw new LogicException('Inspection items are never deleted.'));
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['result' => InspectionItemResult::class];
    }

    /**
     * @return BelongsTo<Inspection, $this>
     */
    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class);
    }
}
