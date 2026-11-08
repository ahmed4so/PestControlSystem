<?php

declare(strict_types=1);

/*
 * The Inspector's checklist (02 §4.8, procedure 6.4–6.6, D-025, D-077).
 * The list is configuration, not code; the labels are English keys shown
 * through __(). The four SOPs are checked on site, not uploaded (D-025).
 */

return [

    'items' => [
        'COMPANY_LICENSE' => [
            'WAREHOUSE_CONDITION' => 'Warehouse condition',
            'VENTILATION' => 'Ventilation',
            'WATERPROOFING_TEMPERATURE' => 'Waterproofing and temperature control',
            'SEPARATION_FROM_FOOD_SEED_FERTILIZER' => 'Separation from food, seed and fertilizer',
            'FIRE_SAFETY' => 'Fire safety',
            'SPILL_SAFETY' => 'Spill safety',
            'EMERGENCY_EXITS' => 'Emergency exits',
            'SECONDARY_CONTAINMENT' => 'Secondary containment',
            'PPE' => 'Personal protective equipment',
            'FIRST_AID' => 'First aid',
            'WASTE_MANAGEMENT' => 'Waste management',
            'SOP_HANDLING_STORAGE' => 'SOP: handling and storage',
            'SOP_SPILLS_WASTE' => 'SOP: spills and waste',
            'SOP_EMERGENCY_FIRST_AID' => 'SOP: emergency and first aid',
            'SOP_STOCK_SALES_RECORDS' => 'SOP: stock and sales records',
            'STOCK_SALES_DISTRIBUTION_RECORDS' => 'Stock, sales and distribution records',
        ],
        // The Operator License adds the fumigation equipment (04 §6).
        'OPERATOR_LICENSE_EXTRA' => [
            'FUMIGATION_EQUIPMENT' => 'Fumigation equipment',
        ],
    ],

    // Photos per checklist item (D-135).
    'max_photos_per_item' => 5,

];
