<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Audit\Concerns\Audited;
use Database\Factories\FederalMemberStateFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A Federal Member State (02 §4.2, D-065).
 *
 * @property int $id
 * @property string $code
 * @property string $name_en
 * @property string $name_so
 * @property int $sort_order
 * @property bool $is_active
 */
class FederalMemberState extends Model
{
    /** @use HasFactory<FederalMemberStateFactory> */
    use Audited, HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = ['code', 'name_en', 'name_so', 'sort_order', 'is_active'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    /**
     * @return HasMany<Region, $this>
     */
    public function regions(): HasMany
    {
        return $this->hasMany(Region::class);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('sort_order')->orderBy('name_en');
    }
}
