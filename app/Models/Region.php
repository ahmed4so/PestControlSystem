<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Audit\Concerns\Audited;
use Database\Factories\RegionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A region within a Federal Member State (02 §4.2, D-065).
 *
 * @property int $id
 * @property int $federal_member_state_id
 * @property string $name_en
 * @property string $name_so
 * @property int $sort_order
 * @property bool $is_active
 */
class Region extends Model
{
    /** @use HasFactory<RegionFactory> */
    use Audited, HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = ['federal_member_state_id', 'name_en', 'name_so', 'sort_order', 'is_active'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    /**
     * @return BelongsTo<FederalMemberState, $this>
     */
    public function federalMemberState(): BelongsTo
    {
        return $this->belongsTo(FederalMemberState::class);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('sort_order')->orderBy('name_en');
    }
}
