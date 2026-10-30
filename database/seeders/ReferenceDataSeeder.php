<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\FederalMemberState;
use Illuminate\Database\Seeder;

/**
 * Federal Member States and regions (02 §4.2, D-065). Values come from
 * data/fms-regions.php, a draft list until SARIS provides the official one
 * (D-184). Existing values are updated by code and name, never deleted.
 */
class ReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        /** @var array{is_draft: bool, states: list<array{code: string, name_en: string, name_so: string, regions: list<array{name_en: string, name_so: string}>}>} $data */
        $data = require __DIR__.'/data/fms-regions.php';

        foreach ($data['states'] as $order => $state) {
            $fms = FederalMemberState::withTrashed()->updateOrCreate(
                ['code' => $state['code']],
                ['name_en' => $state['name_en'], 'name_so' => $state['name_so'], 'sort_order' => $order],
            );

            foreach ($state['regions'] as $regionOrder => $region) {
                $fms->regions()->withTrashed()->updateOrCreate(
                    ['name_en' => $region['name_en']],
                    ['name_so' => $region['name_so'], 'sort_order' => $regionOrder],
                );
            }
        }

        if ($data['is_draft']) {
            $this->command?->warn('Seeded the DRAFT FMS and region list (D-184). Replace it with the official SARIS list before go-live.');
        }
    }
}
