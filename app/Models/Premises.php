<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Audit\Concerns\Audited;
use App\Domain\CompanyRegistry\Concerns\BelongsToCompany;
use App\Domain\CompanyRegistry\Concerns\HasAddress;
use App\Domain\CompanyRegistry\Enums\PremisesType;
use Database\Factories\PremisesFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A company's premises with its facility checklist (02 §4.4, D-018).
 *
 * @property int $id
 * @property string $label
 * @property PremisesType $premises_type
 * @property string|null $gps_lat
 * @property string|null $gps_lng
 * @property string|null $waste_disposal_method
 */
class Premises extends Model
{
    /** @use HasFactory<PremisesFactory> */
    use Audited, BelongsToCompany, HasAddress, HasFactory, SoftDeletes;

    protected $table = 'premises';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'label',
        'premises_type',
        'address_fms_id',
        'address_region_id',
        'address_district',
        'address_town_or_market',
        'address_street',
        'address_details',
        'gps_lat',
        'gps_lng',
        'waste_disposal_method',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'address_fms_id' => 'integer',
            'address_region_id' => 'integer',
            'premises_type' => PremisesType::class,
            'gps_lat' => 'decimal:7',
            'gps_lng' => 'decimal:7',
        ];
    }

    /**
     * @return HasMany<FacilityChecklistItem, $this>
     */
    public function checklistItems(): HasMany
    {
        return $this->hasMany(FacilityChecklistItem::class);
    }

    /**
     * @return HasManyThrough<FacilityChecklistPhoto, FacilityChecklistItem, $this>
     */
    public function photos(): HasManyThrough
    {
        return $this->hasManyThrough(FacilityChecklistPhoto::class, FacilityChecklistItem::class);
    }

    /**
     * Same as checklistItems(); named for scoped route binding of {item}.
     *
     * @return HasMany<FacilityChecklistItem, $this>
     */
    public function items(): HasMany
    {
        return $this->checklistItems();
    }
}
