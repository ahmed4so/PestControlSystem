<?php

declare(strict_types=1);

namespace App\Domain\Workflow\Enums;

/**
 * Workflow actions (02-data-model §5.2, D-099). There is no WITHDRAW
 * (D-101) and no ISSUE_LICENSE (D-106).
 */
enum WorkflowActionCode: string
{
    case Submit = 'SUBMIT';
    case Resubmit = 'RESUBMIT';
    case Acknowledge = 'ACKNOWLEDGE';
    case CompleteAdminReview = 'COMPLETE_ADMIN_REVIEW';
    case CompleteTechnicalReview = 'COMPLETE_TECHNICAL_REVIEW';
    case AssignTechnicalReviewer = 'ASSIGN_TECHNICAL_REVIEWER';
    case ScheduleInspection = 'SCHEDULE_INSPECTION';
    case SubmitInspectionReport = 'SUBMIT_INSPECTION_REPORT';
    case CompleteConsolidatedReview = 'COMPLETE_CONSOLIDATED_REVIEW';
    case Recommend = 'RECOMMEND';
    case ReturnToRegistrar = 'RETURN_TO_REGISTRAR';
    case IssuePermissionToPay = 'ISSUE_PERMISSION_TO_PAY';
    case UploadReceipt = 'UPLOAD_RECEIPT';
    case ClearPayment = 'CLEAR_PAYMENT';
    case RejectReceipt = 'REJECT_RECEIPT';
    case Approve = 'APPROVE';
    case Defer = 'DEFER';
    case Reject = 'REJECT';
    case ReturnToCompany = 'RETURN_TO_COMPANY';
    case RecordInspectionVisit = 'RECORD_INSPECTION_VISIT';
    case RescheduleInspection = 'RESCHEDULE_INSPECTION';

    /**
     * The DG's decisions, which segregation of duties protects (D-086).
     *
     * @return list<self>
     */
    public static function dgDecisions(): array
    {
        return [self::Approve, self::Defer, self::Reject];
    }
}
