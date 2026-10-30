<?php

declare(strict_types=1);

namespace App\Domain\ReferenceData\Support;

use App\Models\FederalMemberState;
use App\Models\Region;

/**
 * The active FMS and region lists for address forms (D-065), in the
 * current language.
 */
final class AddressOptions
{
    /**
     * @return list<array{id: int, name: string, regions: list<array{id: int, name: string}>}>
     */
    public static function forForms(): array
    {
        $somali = app()->getLocale() === 'so';

        return FederalMemberState::query()
            ->active()
            ->with(['regions' => fn ($query) => $query->active()])
            ->get()
            ->map(fn (FederalMemberState $fms): array => [
                'id' => $fms->id,
                'name' => $somali ? $fms->name_so : $fms->name_en,
                'regions' => $fms->regions->map(fn (Region $region): array => [
                    'id' => $region->id,
                    'name' => $somali ? $region->name_so : $region->name_en,
                ])->values()->all(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{code: string, name: string}>
     */
    public static function countries(): array
    {
        $list = [];

        foreach (Countries::all(app()->getLocale()) as $code => $name) {
            $list[] = ['code' => $code, 'name' => $name];
        }

        return $list;
    }
}
