<?php

declare(strict_types=1);

namespace App\Domain\Identity\Support;

/**
 * The single answer each public account form gives, whatever the email
 * and whether or not the submission was refused as spam (03 §4, D-080).
 */
final class PublicFormMessages
{
    public const SIGN_UP = 'Check your email. If the details can be used, we have sent you a message with the next step.';

    public const TEMPORARY_PASSWORD = 'If this email belongs to an account that has not been activated yet, we have sent a new temporary password. It is valid for 1 hour.';

    public const FORGOT_PASSWORD = 'If an account exists for this email, we have sent a password reset link. It is valid for 60 minutes.';

    /**
     * Message for a form, by the route name of its POST endpoint.
     */
    public static function forRoute(?string $routeName): string
    {
        return match (true) {
            $routeName === 'portal.signup.store' => self::SIGN_UP,
            $routeName === 'portal.temporary-password.store' => self::TEMPORARY_PASSWORD,
            default => self::FORGOT_PASSWORD,
        };
    }
}
