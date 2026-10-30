<?php

declare(strict_types=1);

namespace App\Domain\CompanyRegistry\Actions;

use App\Domain\CompanyRegistry\Enums\ChecklistItem;
use App\Models\Company;
use App\Models\Premises;
use Illuminate\Support\Facades\DB;

/**
 * Creates or updates a premises; every premises has one answer row per
 * checklist item (02 §4.4).
 */
final class SavePremises
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __invoke(Company $company, array $data, ?Premises $premises = null): Premises
    {
        return DB::transaction(function () use ($company, $data, $premises): Premises {
            $premises ??= new Premises;
            $premises->fill($data);
            $premises->company()->associate($company);
            $premises->save();

            $existing = $premises->checklistItems()->pluck('item_code')->map(fn (ChecklistItem $code) => $code->value)->all();

            foreach (ChecklistItem::cases() as $item) {
                if (! in_array($item->value, $existing, true)) {
                    $premises->checklistItems()->create(['item_code' => $item, 'is_available' => false]);
                }
            }

            return $premises;
        });
    }
}
