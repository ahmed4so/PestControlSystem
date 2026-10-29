<?php

declare(strict_types=1);

namespace App\Domain\Identity\Support;

use Illuminate\Validation\Rules\Password;

/**
 * Password policy for both realms (D-082, 03-auth-security §6): at least 12
 * characters, upper and lower case, a number, a symbol, and not breached.
 */
final class PasswordPolicy
{
    public const MIN_LENGTH = 12;

    public static function rule(bool $checkBreached = true): Password
    {
        $rule = Password::min(self::MIN_LENGTH)->mixedCase()->letters()->numbers()->symbols();

        return $checkBreached ? $rule->uncompromised() : $rule;
    }

    /**
     * The default rule for every environment except automated tests (D-082).
     * Tests keep Laravel's minimal default so fixtures stay short; the policy
     * itself is tested through rule().
     */
    public static function defaults(): ?Password
    {
        return app()->runningUnitTests() ? null : self::rule();
    }
}
