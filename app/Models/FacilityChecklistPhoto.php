<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Audit\Concerns\Audited;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A photo for a checklist item. Replacing a photo soft-deletes the row;
 * the stored file is kept (02 §4.4).
 *
 * @property int $id
 * @property int $facility_checklist_item_id
 * @property int $stored_file_id
 * @property CarbonImmutable|null $captured_at
 */
class FacilityChecklistPhoto extends Model
{
    use Audited, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = ['stored_file_id', 'captured_at'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['captured_at' => 'datetime'];
    }

    /**
     * @return BelongsTo<FacilityChecklistItem, $this>
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(FacilityChecklistItem::class, 'facility_checklist_item_id');
    }

    /**
     * @return BelongsTo<StoredFile, $this>
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(StoredFile::class, 'stored_file_id');
    }
}
