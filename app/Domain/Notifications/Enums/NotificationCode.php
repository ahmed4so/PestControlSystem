<?php

declare(strict_types=1);

namespace App\Domain\Notifications\Enums;

use App\Domain\Notifications\Mail\NotificationMail;
use Illuminate\Support\Str;

/**
 * Email codes from the notification catalogue (07 §3, D-128). Phase 1 adds
 * the account codes of §3.1, Phase 2 the company-data codes of §3.2; later
 * phases add their own.
 */
enum NotificationCode: string
{
    case CompanyTempPassword = 'COMPANY_TEMP_PASSWORD';
    case CompanyAccountExists = 'COMPANY_ACCOUNT_EXISTS';
    case StaffTempPassword = 'STAFF_TEMP_PASSWORD';
    case PasswordResetLink = 'PASSWORD_RESET_LINK';
    case PasswordChanged = 'PASSWORD_CHANGED';
    case PasswordExpiryReminder = 'PASSWORD_EXPIRY_REMINDER';
    case AccountLocked = 'ACCOUNT_LOCKED';
    case TwoFactorChanged = 'TWO_FACTOR_CHANGED';
    case EmailChangeConfirm = 'EMAIL_CHANGE_CONFIRM';
    case EmailChangeNotice = 'EMAIL_CHANGE_NOTICE';
    case CertificateUpdated = 'CERTIFICATE_UPDATED';
    case NationalIdReleased = 'NATIONAL_ID_RELEASED';

    // Application workflow (07 §3.3). Codes of later phases are listed so
    // definitions validate; they are sent once their phase builds them.
    case ApplicationSubmitted = 'APPLICATION_SUBMITTED';
    case WorkItemReady = 'WORK_ITEM_READY';
    case ApplicationAcknowledged = 'APPLICATION_ACKNOWLEDGED';
    case ApplicationReturned = 'APPLICATION_RETURNED';
    case ApplicationResubmitted = 'APPLICATION_RESUBMITTED';
    case ReviewerAssigned = 'REVIEWER_ASSIGNED';
    case TechnicalReviewDone = 'TECHNICAL_REVIEW_DONE';
    case InspectionScheduled = 'INSPECTION_SCHEDULED';
    case InspectionRescheduled = 'INSPECTION_RESCHEDULED';
    case PaymentRequired = 'PAYMENT_REQUIRED';
    case ReceiptRejected = 'RECEIPT_REJECTED';
    case PaymentCleared = 'PAYMENT_CLEARED';
    case LicenseIssued = 'LICENSE_ISSUED';
    case ApplicationRejected = 'APPLICATION_REJECTED';

    /**
     * Codes listed for the workflow definitions whose email comes with a
     * later phase (D-188): payment,
     * license and rejection in Phase 4B.
     */
    public function isBuiltLater(): bool
    {
        return in_array($this, [
            self::PaymentRequired,
            self::ReceiptRejected,
            self::PaymentCleared,
            self::LicenseIssued,
            self::ApplicationRejected,
        ], true);
    }

    /**
     * Short subject in English; translated into the recipient's language.
     */
    public function subject(): string
    {
        return match ($this) {
            self::CompanyTempPassword => 'Your temporary password',
            self::CompanyAccountExists => 'You already have an account',
            self::StaffTempPassword => 'Your SARIS staff account',
            self::PasswordResetLink => 'Reset your password',
            self::PasswordChanged => 'Your password was changed',
            self::PasswordExpiryReminder => 'Your password expires soon',
            self::AccountLocked => 'Your account was locked',
            self::TwoFactorChanged => 'Two-factor authentication changed',
            self::EmailChangeConfirm => 'Confirm your new login email',
            self::EmailChangeNotice => 'A change of login email was requested',
            self::CertificateUpdated => 'A renewed certificate was uploaded',
            self::NationalIdReleased => 'A person was removed from your profile',
            self::ApplicationSubmitted => 'Application received',
            self::WorkItemReady => 'An application is waiting for you',
            self::ApplicationAcknowledged => 'Your application is under review',
            self::ApplicationReturned => 'Action required on your application',
            self::ApplicationResubmitted => 'An application was resubmitted',
            self::ReviewerAssigned => 'You are assigned to an application',
            self::TechnicalReviewDone => 'Technical review completed',
            self::InspectionScheduled => 'Inspection planned',
            self::InspectionRescheduled => 'Inspection moved',
            self::PaymentRequired => 'Permission to pay issued',
            self::ReceiptRejected => 'Receipt not accepted',
            self::PaymentCleared => 'Payment confirmed',
            self::LicenseIssued => 'Your license was issued',
            self::ApplicationRejected => 'Your application was rejected',
        };
    }

    /**
     * Blade view for one language of the message (07 §4).
     */
    public function view(): string
    {
        return 'mail.'.$this->value;
    }

    public function textView(): string
    {
        return 'mail.'.$this->value.'-text';
    }

    /**
     * The Mailable for this code: one class per code (07 §4).
     *
     * @return class-string<NotificationMail>
     */
    public function mailable(): string
    {
        /** @var class-string<NotificationMail> */
        return 'App\\Domain\\Notifications\\Mail\\'.Str::studly(strtolower($this->value));
    }
}
