<?php

declare(strict_types=1);

namespace App\Domain\Applications\Enums;

/**
 * Application statuses (02-data-model §5.1, D-022, D-045, D-101, D-106).
 * Each status is also a workflow stage code (04 §5.1).
 */
enum ApplicationStatus: string
{
    case Draft = 'DRAFT';
    case Submitted = 'SUBMITTED';
    case AdminReview = 'ADMIN_REVIEW';
    case TechnicalDocReview = 'TECHNICAL_DOC_REVIEW';
    case InspectionScheduled = 'INSPECTION_SCHEDULED';
    case InspectionReport = 'INSPECTION_REPORT';
    case ConsolidatedReview = 'CONSOLIDATED_REVIEW';
    case DirectorReview = 'DIRECTOR_REVIEW';
    case PermissionToPay = 'PERMISSION_TO_PAY';
    case AwaitingPayment = 'AWAITING_PAYMENT';
    case PaymentVerification = 'PAYMENT_VERIFICATION';
    case DgDecision = 'DG_DECISION';
    case Issued = 'ISSUED';
    case Returned = 'RETURNED';
    case Rejected = 'REJECTED';

    /**
     * The simplified status a company sees (D-045, 02 §5.1).
     */
    public function companyLabel(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Submitted => 'Submitted',
            self::InspectionScheduled => 'Inspection Scheduled',
            self::AwaitingPayment => 'Payment Required',
            self::PaymentVerification => 'Payment Under Verification',
            self::Issued => 'Approved',
            self::Returned => 'Action Required',
            self::Rejected => 'Rejected',
            default => 'Under Review',
        };
    }

    /**
     * ISSUED and REJECTED end an application (D-101: no withdrawal).
     */
    public function isFinal(): bool
    {
        return $this === self::Issued || $this === self::Rejected;
    }
}
