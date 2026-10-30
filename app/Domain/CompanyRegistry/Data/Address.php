<?php

declare(strict_types=1);

namespace App\Domain\CompanyRegistry\Data;

use Illuminate\Database\Eloquent\Model;

/**
 * The structured address (02 §4.9, D-065), as a value that can be copied
 * as-is into snapshots.
 */
final readonly class Address
{
    public function __construct(
        public ?int $fmsId,
        public ?int $regionId,
        public ?string $district,
        public ?string $townOrMarket,
        public ?string $street,
        public ?string $details,
    ) {}

    public static function fromModel(Model $model): self
    {
        return new self(
            $model->getAttribute('address_fms_id'),
            $model->getAttribute('address_region_id'),
            $model->getAttribute('address_district'),
            $model->getAttribute('address_town_or_market'),
            $model->getAttribute('address_street'),
            $model->getAttribute('address_details'),
        );
    }

    /**
     * Required parts present (05 §4.5): FMS, region, town or market.
     */
    public function isComplete(): bool
    {
        return $this->fmsId !== null && $this->regionId !== null && filled($this->townOrMarket);
    }

    /**
     * @return array<string, int|string|null>
     */
    public function toArray(): array
    {
        return [
            'address_fms_id' => $this->fmsId,
            'address_region_id' => $this->regionId,
            'address_district' => $this->district,
            'address_town_or_market' => $this->townOrMarket,
            'address_street' => $this->street,
            'address_details' => $this->details,
        ];
    }
}
