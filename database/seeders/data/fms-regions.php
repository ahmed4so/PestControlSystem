<?php

declare(strict_types=1);

/*
| DRAFT list of Federal Member States and regions, for development and
| testing only (D-184). SARIS has not yet provided the official list (open
| question 12). Replace this file, or edit the values in the admin screens,
| before go-live.
*/

return [
    'is_draft' => true,
    'states' => [
        ['code' => 'BANADIR', 'name_en' => 'Banadir', 'name_so' => 'Banaadir', 'regions' => [
            ['name_en' => 'Banadir', 'name_so' => 'Banaadir'],
        ]],
        ['code' => 'GALMUDUG', 'name_en' => 'Galmudug', 'name_so' => 'Galmudug', 'regions' => [
            ['name_en' => 'Galgaduud', 'name_so' => 'Galgaduud'],
            ['name_en' => 'Mudug', 'name_so' => 'Mudug'],
        ]],
        ['code' => 'HIRSHABELLE', 'name_en' => 'Hirshabelle', 'name_so' => 'Hirshabeelle', 'regions' => [
            ['name_en' => 'Hiiraan', 'name_so' => 'Hiiraan'],
            ['name_en' => 'Middle Shabelle', 'name_so' => 'Shabeellaha Dhexe'],
        ]],
        ['code' => 'JUBALAND', 'name_en' => 'Jubaland', 'name_so' => 'Jubbaland', 'regions' => [
            ['name_en' => 'Gedo', 'name_so' => 'Gedo'],
            ['name_en' => 'Middle Juba', 'name_so' => 'Jubbada Dhexe'],
            ['name_en' => 'Lower Juba', 'name_so' => 'Jubbada Hoose'],
        ]],
        ['code' => 'PUNTLAND', 'name_en' => 'Puntland', 'name_so' => 'Puntland', 'regions' => [
            ['name_en' => 'Bari', 'name_so' => 'Bari'],
            ['name_en' => 'Nugaal', 'name_so' => 'Nugaal'],
        ]],
        ['code' => 'SOUTH_WEST', 'name_en' => 'South West', 'name_so' => 'Koonfur Galbeed', 'regions' => [
            ['name_en' => 'Bay', 'name_so' => 'Baay'],
            ['name_en' => 'Bakool', 'name_so' => 'Bakool'],
            ['name_en' => 'Lower Shabelle', 'name_so' => 'Shabeellaha Hoose'],
        ]],
    ],
];
