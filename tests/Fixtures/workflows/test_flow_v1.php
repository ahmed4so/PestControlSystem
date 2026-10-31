<?php

declare(strict_types=1);

/*
 * A small workflow for engine tests. It uses only guards and effects built
 * in Phase 3, plus PaymentCleared for the conditional target (tests replace
 * it in the registry).
 */

return [
    'code' => 'TEST_FLOW_V1',
    'service' => 'COMPANY_LICENSE',
    'version' => 1,

    'stages' => [
        ['code' => 'DRAFT', 'actor' => 'COMPANY'],
        ['code' => 'SUBMITTED', 'actor' => 'REGISTRAR'],
        ['code' => 'ADMIN_REVIEW', 'actor' => 'REGISTRAR', 'review_items' => ['ITEM_A', 'ITEM_B']],
        ['code' => 'TECHNICAL_DOC_REVIEW', 'actor' => 'REGISTRAR', 'review_items' => ['ITEM_T']],
        ['code' => 'CONSOLIDATED_REVIEW', 'actor' => 'REGISTRAR'],
        ['code' => 'DIRECTOR_REVIEW', 'actor' => 'DEPARTMENTAL_DIRECTOR'],
        ['code' => 'PAYMENT_VERIFICATION', 'actor' => 'PAYMENT_OFFICER'],
        ['code' => 'DG_DECISION', 'actor' => 'DIRECTOR_GENERAL'],
        ['code' => 'ISSUED', 'final' => true],
        ['code' => 'RETURNED', 'actor' => 'COMPANY'],
        ['code' => 'REJECTED', 'final' => true],
    ],

    'transitions' => [
        ['from' => 'DRAFT', 'action' => 'SUBMIT', 'actor' => 'COMPANY', 'to' => 'SUBMITTED', 'password' => true,
            'guards' => ['NoOtherOpenApplication'], 'effects' => ['GenerateReference', 'CreateVersionSnapshot']],
        ['from' => 'SUBMITTED', 'action' => 'ACKNOWLEDGE', 'actor' => 'REGISTRAR', 'to' => 'ADMIN_REVIEW'],
        ['from' => 'ADMIN_REVIEW', 'action' => 'COMPLETE_ADMIN_REVIEW', 'actor' => 'REGISTRAR', 'to' => 'TECHNICAL_DOC_REVIEW',
            'guards' => ['ReviewItemsAllCompliant:ADMIN_REVIEW']],
        ['from' => 'ADMIN_REVIEW', 'action' => 'RETURN_TO_COMPANY', 'actor' => 'REGISTRAR', 'to' => 'RETURNED', 'comment' => true,
            'guards' => ['ReviewItemsHaveDeficiency:ADMIN_REVIEW'], 'effects' => ['SetResumeStage:ADMIN_REVIEW']],
        ['from' => 'TECHNICAL_DOC_REVIEW', 'action' => 'ASSIGN_TECHNICAL_REVIEWER', 'actor' => 'REGISTRAR', 'to' => 'same',
            'guards' => ['AssigneeHasRole:TECHNICAL_REVIEWER'], 'effects' => ['CreateAssignment']],
        ['from' => 'TECHNICAL_DOC_REVIEW', 'action' => 'COMPLETE_TECHNICAL_REVIEW', 'actor' => 'TECHNICAL_REVIEWER', 'to' => 'same',
            'guards' => ['ReviewItemsRecorded:TECHNICAL_DOC_REVIEW'], 'effects' => ['EndAssignment']],
        ['from' => 'TECHNICAL_DOC_REVIEW', 'action' => 'SCHEDULE_INSPECTION', 'actor' => 'REGISTRAR', 'to' => 'CONSOLIDATED_REVIEW',
            'guards' => ['NoOpenAssignment']],
        ['from' => 'CONSOLIDATED_REVIEW', 'action' => 'COMPLETE_CONSOLIDATED_REVIEW', 'actor' => 'REGISTRAR',
            'to' => ['DG_DECISION' => 'PaymentCleared', 'default' => 'DIRECTOR_REVIEW']],
        ['from' => 'DIRECTOR_REVIEW', 'action' => 'RECOMMEND', 'actor' => 'DEPARTMENTAL_DIRECTOR', 'to' => 'PAYMENT_VERIFICATION',
            'guards' => ['Segregation']],
        ['from' => 'PAYMENT_VERIFICATION', 'action' => 'CLEAR_PAYMENT', 'actor' => 'PAYMENT_OFFICER', 'to' => 'DG_DECISION', 'password' => true],
        ['from' => 'DG_DECISION', 'action' => 'APPROVE', 'actor' => 'DIRECTOR_GENERAL', 'to' => 'ISSUED', 'password' => true,
            'guards' => ['ActiveDelegation'], 'effects' => ['ClearOpenKey', 'SetDecidedAt']],
        ['from' => 'DG_DECISION', 'action' => 'DEFER', 'actor' => 'DIRECTOR_GENERAL', 'to' => 'RETURNED', 'comment' => true,
            'effects' => ['SetResumeStage:CONSOLIDATED_REVIEW']],
        ['from' => 'DG_DECISION', 'action' => 'REJECT', 'actor' => 'DIRECTOR_GENERAL', 'to' => 'REJECTED', 'password' => true, 'comment' => true,
            'effects' => ['ClearOpenKey', 'SetDecidedAt']],
        ['from' => 'RETURNED', 'action' => 'RESUBMIT', 'actor' => 'COMPANY', 'to' => 'resume_stage', 'password' => true,
            'effects' => ['CreateVersionSnapshot', 'ClearResumeStage']],
    ],
];
