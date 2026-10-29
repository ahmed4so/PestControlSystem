<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LogicException;

/**
 * An immutable file in R2 (02-data-model §4.3, D-056). Created only by the
 * Documents module's FileStorage; never updated or deleted.
 */
class StoredFile extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size_bytes',
        'sha256',
        'uploaded_by_type',
        'uploaded_by_id',
        'purpose',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'created_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Stored files are immutable.'));
        static::deleting(fn () => throw new LogicException('Stored files are never deleted.'));
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function uploadedBy(): MorphTo
    {
        return $this->morphTo();
    }
}
