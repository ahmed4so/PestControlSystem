<?php

declare(strict_types=1);

/*
 * Company License workflow, version 1 (04 §5, written procedure 6.1–6.13,
 * D-022). Loaded with `php artisan workflows:load` (D-071, D-097).
 *
 * Once an application uses a version, it never changes (D-189, D-202): to
 * change this workflow then, copy this file to company_license_v2.php with
 * 'version' => 2 and load it. Running applications keep their version.
 *
 * Differences from the 04 §5.2 table, from later decisions:
 * - no ChecklistPhotos guard on SUBMIT or RESUBMIT: the facility checklist
 *   is the Inspector's job (D-192); PremisesSelected checks that at least
 *   one premises is chosen instead (D-197);
 * - no SetOpenKey on SUBMIT: the draft holds the key from creation (D-193).
 *
 * Emails use the 07 §3.3 codes. WORK_ITEM_READY goes out whenever the
 * application enters a new staff stage (D-198); on RESUBMIT the stage's
 * staff get APPLICATION_RESUBMITTED instead, so they don't get two emails.
 *
 * Targets: 'same' keeps the stage; 'resume_stage' continues where the
 * application was returned (D-043); a map is a conditional target,
 * evaluated in order after the effects, with a 'default' (04 §13).
 */

$submitGuards = [
    'ProfileComplete',
    'CertificatesValid',
    'RequiredDocuments',
    'TechnicalPersonSelected',
    'PremisesSelected',
    'NoOtherOpenApplication',
    'RenewalWindowOpen',
    'PreviousLicenseActive',
];

return [
    'code' => 'COMPANY_LICENSE_V1',
    'service' => 'COMPANY_LICENSE',
    'version' => 1,

    'stages' => [
        ['code' => 'DRAFT', 'actor' => 'COMPANY', 'name_en' => 'Draft', 'name_so' => 'Qabyo'],
        ['code' => 'SUBMITTED', 'actor' => 'REGISTRAR', 'name_en' => 'Submitted', 'name_so' => 'La gudbiyay'],
        [
            'code' => 'ADMIN_REVIEW', 'actor' => 'REGISTRAR', 'name_en' => 'Administrative review', 'name_so' => 'Dib-u-eegis maamul',
            'review_items' => [
                'COMMERCE_REGISTRATION',
                'TAX_COMPLIANCE',
                'IDENTIFICATION',
                'COMPANY_PROFILE',
                'CATEGORY',
                'APPLICATION_COMPLETENESS',
                'SUPPORTING_DOCUMENTS',
            ],
        ],
        [
            'code' => 'TECHNICAL_DOC_REVIEW', 'actor' => 'REGISTRAR', 'name_en' => 'Technical document review', 'name_so' => 'Dib-u-eegis dukumentiyada farsamada',
            'review_items' => ['TECHNICAL_QUALIFICATION', 'TECHNICAL_CV'],
        ],
        ['code' => 'INSPECTION_SCHEDULED', 'actor' => 'INSPECTOR', 'name_en' => 'Inspection scheduled', 'name_so' => 'Kormeer la qorsheeyay'],
        ['code' => 'INSPECTION_REPORT', 'actor' => 'INSPECTOR', 'name_en' => 'Inspection report', 'name_so' => 'Warbixinta kormeerka'],
        [
            'code' => 'CONSOLIDATED_REVIEW', 'actor' => 'REGISTRAR', 'name_en' => 'Consolidated review', 'name_so' => 'Dib-u-eegis guud',
            'review_items' => [
                'DOCUMENTS_CONSISTENT',
                'INSPECTION_REPORT_PRESENT',
                'INSPECTION_FINDINGS_ADDRESSED',
                'OVERALL_COMPLIANCE',
            ],
        ],
        ['code' => 'DIRECTOR_REVIEW', 'actor' => 'DEPARTMENTAL_DIRECTOR', 'name_en' => 'Director review', 'name_so' => 'Dib-u-eegista agaasimaha'],
        ['code' => 'PERMISSION_TO_PAY', 'actor' => 'PAYMENT_OFFICER', 'name_en' => 'Permission to pay', 'name_so' => 'Oggolaanshaha lacag bixinta'],
        ['code' => 'AWAITING_PAYMENT', 'actor' => 'COMPANY', 'name_en' => 'Awaiting payment', 'name_so' => 'Sugitaanka lacag bixinta'],
        ['code' => 'PAYMENT_VERIFICATION', 'actor' => 'PAYMENT_OFFICER', 'name_en' => 'Payment verification', 'name_so' => 'Xaqiijinta lacag bixinta'],
        ['code' => 'DG_DECISION', 'actor' => 'DIRECTOR_GENERAL', 'name_en' => 'Director General decision', 'name_so' => 'Go\'aanka Agaasimaha Guud'],
        ['code' => 'ISSUED', 'final' => true, 'name_en' => 'Issued', 'name_so' => 'La bixiyay'],
        ['code' => 'RETURNED', 'actor' => 'COMPANY', 'name_en' => 'Returned', 'name_so' => 'La celiyay'],
        ['code' => 'REJECTED', 'final' => true, 'name_en' => 'Rejected', 'name_so' => 'La diiday'],
    ],

    'transitions' => [
        // T1
        [
            'from' => 'DRAFT', 'action' => 'SUBMIT', 'actor' => 'COMPANY', 'to' => 'SUBMITTED', 'password' => true,
            'guards' => $submitGuards,
            'effects' => ['GenerateReference', 'CreateVersionSnapshot', 'SetLateRenewalFlag', 'Notify:APPLICATION_SUBMITTED', 'Notify:WORK_ITEM_READY'],
        ],
        // T2
        [
            'from' => 'SUBMITTED', 'action' => 'ACKNOWLEDGE', 'actor' => 'REGISTRAR', 'to' => 'ADMIN_REVIEW',
            'effects' => ['Notify:APPLICATION_ACKNOWLEDGED', 'Notify:WORK_ITEM_READY'],
        ],
        // T3
        [
            'from' => 'ADMIN_REVIEW', 'action' => 'COMPLETE_ADMIN_REVIEW', 'actor' => 'REGISTRAR', 'to' => 'TECHNICAL_DOC_REVIEW',
            'review_items' => true,
            'guards' => ['ReviewItemsAllCompliant:ADMIN_REVIEW'],
            'effects' => ['Notify:WORK_ITEM_READY'],
        ],
        // T4
        [
            'from' => 'ADMIN_REVIEW', 'action' => 'RETURN_TO_COMPANY', 'actor' => 'REGISTRAR', 'to' => 'RETURNED',
            'comment' => true, 'review_items' => true,
            'guards' => ['ReviewItemsHaveDeficiency:ADMIN_REVIEW'],
            'effects' => ['SetResumeStage:ADMIN_REVIEW', 'Notify:APPLICATION_RETURNED'],
        ],
        // T5
        [
            'from' => 'TECHNICAL_DOC_REVIEW', 'action' => 'ASSIGN_TECHNICAL_REVIEWER', 'actor' => 'REGISTRAR', 'to' => 'same',
            'guards' => ['AssigneeHasRole:TECHNICAL_REVIEWER'],
            'effects' => ['CreateAssignment', 'Notify:REVIEWER_ASSIGNED'],
        ],
        // T6
        [
            'from' => 'TECHNICAL_DOC_REVIEW', 'action' => 'COMPLETE_TECHNICAL_REVIEW', 'actor' => 'TECHNICAL_REVIEWER', 'to' => 'same',
            'review_items' => true,
            'guards' => ['ReviewItemsRecorded:TECHNICAL_DOC_REVIEW'],
            'effects' => ['EndAssignment', 'Notify:TECHNICAL_REVIEW_DONE'],
        ],
        // T7
        [
            'from' => 'TECHNICAL_DOC_REVIEW', 'action' => 'SCHEDULE_INSPECTION', 'actor' => 'REGISTRAR', 'to' => 'INSPECTION_SCHEDULED',
            'guards' => ['ReviewItemsAllCompliant:TECHNICAL_DOC_REVIEW', 'NoOpenAssignment', 'InspectionPlanProvided'],
            'effects' => ['CreateInspections', 'Notify:INSPECTION_SCHEDULED', 'Notify:WORK_ITEM_READY'],
        ],
        // T8
        [
            'from' => 'TECHNICAL_DOC_REVIEW', 'action' => 'RETURN_TO_COMPANY', 'actor' => 'REGISTRAR', 'to' => 'RETURNED',
            'comment' => true, 'review_items' => true,
            'guards' => ['ReviewItemsHaveDeficiency:TECHNICAL_DOC_REVIEW'],
            'effects' => ['SetResumeStage:TECHNICAL_DOC_REVIEW', 'Notify:APPLICATION_RETURNED'],
        ],
        // T9
        [
            'from' => 'INSPECTION_SCHEDULED', 'action' => 'RESCHEDULE_INSPECTION', 'actor' => 'REGISTRAR', 'to' => 'same',
            'comment' => true,
            'effects' => ['UpdateInspectionDate', 'Notify:INSPECTION_RESCHEDULED'],
        ],
        // T10
        [
            'from' => 'INSPECTION_SCHEDULED', 'action' => 'RECORD_INSPECTION_VISIT', 'actor' => 'INSPECTOR', 'to' => 'INSPECTION_REPORT',
            'effects' => ['SetConductedAt', 'Notify:WORK_ITEM_READY'],
        ],
        // T11: on to consolidated review once the last inspection is signed.
        [
            'from' => 'INSPECTION_REPORT', 'action' => 'SUBMIT_INSPECTION_REPORT', 'actor' => 'INSPECTOR', 'password' => true,
            'to' => ['CONSOLIDATED_REVIEW' => 'InspectionsSigned', 'default' => 'same'],
            'guards' => ['InspectionReportComplete'],
            'effects' => ['SignInspection', 'Notify:WORK_ITEM_READY'],
        ],
        // T12: straight to the DG when a payment is already cleared (D-043).
        [
            'from' => 'CONSOLIDATED_REVIEW', 'action' => 'COMPLETE_CONSOLIDATED_REVIEW', 'actor' => 'REGISTRAR',
            'to' => ['DG_DECISION' => 'PaymentCleared', 'default' => 'DIRECTOR_REVIEW'],
            'review_items' => true,
            'guards' => ['ReviewItemsAllCompliant:CONSOLIDATED_REVIEW', 'InspectionsSigned'],
            'effects' => ['Notify:WORK_ITEM_READY'],
        ],
        // T13
        [
            'from' => 'CONSOLIDATED_REVIEW', 'action' => 'RETURN_TO_COMPANY', 'actor' => 'REGISTRAR', 'to' => 'RETURNED',
            'comment' => true, 'review_items' => true,
            'guards' => ['ReviewItemsHaveDeficiency:CONSOLIDATED_REVIEW'],
            'effects' => ['SetResumeStage:CONSOLIDATED_REVIEW', 'Notify:APPLICATION_RETURNED'],
        ],
        // T14: re-inspection.
        [
            'from' => 'CONSOLIDATED_REVIEW', 'action' => 'SCHEDULE_INSPECTION', 'actor' => 'REGISTRAR', 'to' => 'INSPECTION_SCHEDULED',
            'comment' => true,
            'guards' => ['InspectionPlanProvided'],
            'effects' => ['CreateInspections', 'Notify:INSPECTION_SCHEDULED', 'Notify:WORK_ITEM_READY'],
        ],
        // T15
        [
            'from' => 'DIRECTOR_REVIEW', 'action' => 'RECOMMEND', 'actor' => 'DEPARTMENTAL_DIRECTOR', 'to' => 'PERMISSION_TO_PAY',
            'guards' => ['Segregation'],
            'effects' => ['Notify:WORK_ITEM_READY'],
        ],
        // T16
        [
            'from' => 'DIRECTOR_REVIEW', 'action' => 'RETURN_TO_REGISTRAR', 'actor' => 'DEPARTMENTAL_DIRECTOR', 'to' => 'CONSOLIDATED_REVIEW',
            'comment' => true,
            'effects' => ['Notify:WORK_ITEM_READY'],
        ],
        // T17
        [
            'from' => 'PERMISSION_TO_PAY', 'action' => 'ISSUE_PERMISSION_TO_PAY', 'actor' => 'PAYMENT_OFFICER', 'to' => 'AWAITING_PAYMENT',
            'guards' => ['FeeScheduleExists', 'Segregation'],
            'effects' => ['IssuePermissionToPayLetter', 'Notify:PAYMENT_REQUIRED'],
        ],
        // T18
        [
            'from' => 'AWAITING_PAYMENT', 'action' => 'UPLOAD_RECEIPT', 'actor' => 'COMPANY', 'to' => 'PAYMENT_VERIFICATION',
            'effects' => ['RecordPayment', 'Notify:WORK_ITEM_READY'],
        ],
        // T19
        [
            'from' => 'PAYMENT_VERIFICATION', 'action' => 'CLEAR_PAYMENT', 'actor' => 'PAYMENT_OFFICER', 'to' => 'DG_DECISION', 'password' => true,
            'guards' => ['AmountMatchesLetter', 'ReceiptNotUsed'],
            'effects' => ['ClearPayment', 'Notify:PAYMENT_CLEARED', 'Notify:WORK_ITEM_READY'],
        ],
        // T20
        [
            'from' => 'PAYMENT_VERIFICATION', 'action' => 'REJECT_RECEIPT', 'actor' => 'PAYMENT_OFFICER', 'to' => 'AWAITING_PAYMENT',
            'comment' => true,
            'effects' => ['RejectPayment', 'Notify:RECEIPT_REJECTED'],
        ],
        // T21
        [
            'from' => 'DG_DECISION', 'action' => 'APPROVE', 'actor' => 'DIRECTOR_GENERAL', 'to' => 'ISSUED', 'password' => true,
            'guards' => ['PaymentCleared', 'Segregation', 'ActiveDelegation', 'SignerHasSignature'],
            'effects' => ['IssueLicense', 'SupersedePreviousLicense', 'ClearAmendmentFlag', 'ClearOpenKey', 'SetDecidedAt', 'Notify:LICENSE_ISSUED'],
        ],
        // T22: the DG defers; on resubmission the file continues at consolidated review (D-043).
        [
            'from' => 'DG_DECISION', 'action' => 'DEFER', 'actor' => 'DIRECTOR_GENERAL', 'to' => 'RETURNED',
            'comment' => true,
            'effects' => ['SetResumeStage:CONSOLIDATED_REVIEW', 'Notify:APPLICATION_RETURNED'],
        ],
        // T23
        [
            'from' => 'DG_DECISION', 'action' => 'REJECT', 'actor' => 'DIRECTOR_GENERAL', 'to' => 'REJECTED', 'password' => true,
            'comment' => true,
            'effects' => ['ClearOpenKey', 'SetDecidedAt', 'Notify:APPLICATION_REJECTED'],
        ],
        // T25 (T24, withdrawal, was removed by D-101)
        [
            'from' => 'RETURNED', 'action' => 'RESUBMIT', 'actor' => 'COMPANY', 'to' => 'resume_stage', 'password' => true,
            'guards' => $submitGuards,
            'effects' => ['CreateVersionSnapshot', 'ClearResumeStage', 'Notify:APPLICATION_RESUBMITTED'],
        ],
    ],
];
