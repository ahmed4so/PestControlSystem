<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Audit\Concerns\Audited;
use App\Domain\CompanyRegistry\Enums\ChecklistItem;
use Database\Factories\FacilityChecklistItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * One checklist answer for a premises (02 §4.4, D-018).
 *
 * @property int $id
 * @property int $premises_id
 * @property ChecklistItem $item_code
 * @property bool $is_available
 * @property string|null $notes
 */
class FacilityChecklistItem extends Model
{
    /** @use HasFactory<FacilityChecklistItemFactory> */
    use Audited, HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = ['item_code', 'is_available', 'notes'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'item_code' => ChecklistItem::class,
            'is_available' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Premises, $this>
     */
    public function premises(): BelongsTo
    {
        return $this->belongsTo(Premises::class);
    }

    /**
     * @return HasMany<FacilityChecklistPhoto, $this>
     */
    public function photos(): HasMany
    {
        return $this->hasMany(FacilityChecklistPhoto::class);
    }
}
