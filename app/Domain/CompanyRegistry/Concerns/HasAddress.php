<?php

declare(strict_types=1);

namespace App\Domain\CompanyRegistry\Concerns;

use App\Domain\CompanyRegistry\Data\Address;
use App\Models\FederalMemberState;
use App\Models\Region;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The embedded address columns (02 §4.9, D-065).
 */
trait HasAddress
{
    public const ADDRESS_FIELDS = [
        'address_fms_id',
        'address_region_id',
        'address_district',
        'address_town_or_market',
        'address_street',
        'address_details',
    ];

    /**
     * @return BelongsTo<FederalMemberState, $this>
     */
    public function addressFms(): BelongsTo
    {
        return $this->belongsTo(FederalMemberState::class, 'address_fms_id')->withTrashed();
    }

    /**
     * @return BelongsTo<Region, $this>
     */
    public function addressRegion(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'address_region_id')->withTrashed();
    }

    public function address(): Address
    {
        return Address::fromModel($this);
    }
}
