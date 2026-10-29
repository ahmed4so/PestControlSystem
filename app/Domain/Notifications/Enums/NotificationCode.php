<?php

declare(strict_types=1);

namespace App\Domain\Notifications\Enums;

use App\Domain\Notifications\Mail\NotificationMail;
use Illuminate\Support\Str;

/**
 * Email codes from the notification catalogue (07 §3, D-128). Phase 1 uses
 * the account and security codes of §3.1; later phases add their own.
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
