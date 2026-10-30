<?php

declare(strict_types=1);

namespace App\Domain\Identity\Support;

/**
 * Temporary passwords (03 §4): 12 random letters and digits without
 * look-alike characters, from a cryptographic source. Only the hash is
 * stored; the plain value goes into one email.
 */
final class TemporaryPassword
{
    public const ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';

    public static function generate(): string
    {
        $length = (int) config('identity.temporary_password.length', 12);
        $max = strlen(self::ALPHABET) - 1;
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= self::ALPHABET[random_int(0, $max)];
        }

        return $password;
    }
}
