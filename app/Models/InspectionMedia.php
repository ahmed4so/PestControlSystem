<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Audit\Concerns\Audited;
use App\Domain\Inspection\Enums\MediaType;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use LogicException;

/**
 * A photo or video taken by the Inspector (02 §4.8, D-135), optionally for
 * one checklist item. Files live in the Documents module; media are never
 * changed or deleted.
 *
 * @property int $id
 * @property int $inspection_id
 * @property int|null $inspection_item_id
 * @property int $stored_file_id
 * @property MediaType $media_type
 * @property string|null $gps_lat
 * @property string|null $gps_lng
 * @property CarbonImmutable|null $captured_at
 */
class InspectionMedia extends Model
{
    use Audited, SoftDeletes;

    protected $table = 'inspection_media';

    /**
     * @var list<string>
     */
    protected $fillable = ['media_type', 'gps_lat', 'gps_lng', 'captured_at'];

    protected static function booted(): void
    {
        static::creating(function (InspectionMedia $media): void {
            if (Inspection::query()->whereKey($media->inspection_id)->whereNotNull('signed_at')->exists()) {
                throw new LogicException('A signed inspection report cannot be changed.');
            }
        });
        static::updating(fn () => throw new LogicException('Inspection media are never changed.'));
        static::deleting(fn () => throw new LogicException('Inspection media are never deleted.'));
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['media_type' => MediaType::class, 'captured_at' => 'datetime'];
    }

    /**
     * @return BelongsTo<Inspection, $this>
     */
    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class);
    }

    /**
     * @return BelongsTo<InspectionItem, $this>
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(InspectionItem::class, 'inspection_item_id');
    }

    /**
     * @return BelongsTo<StoredFile, $this>
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(StoredFile::class, 'stored_file_id');
    }
}
