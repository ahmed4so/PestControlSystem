<?php

declare(strict_types=1);

namespace App\Domain\Audit\Support;

/**
 * Masks a sensitive value for the audit log, keeping the last four
 * characters, e.g. "****1234" (03-auth-security §12.1).
 */
final class Mask
{
    public static function value(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = (string) $value;

        return '****'.(mb_strlen($value) > 4 ? mb_substr($value, -4) : '');
    }
}
