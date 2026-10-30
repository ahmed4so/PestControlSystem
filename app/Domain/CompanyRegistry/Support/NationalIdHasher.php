<?php

declare(strict_types=1);

namespace App\Domain\CompanyRegistry\Support;

use RuntimeException;

/**
 * HMAC-SHA256 of a normalised national ID (D-088, D-117). The hash is the
 * lookup and uniqueness key; the ID itself is stored encrypted.
 */
final class NationalIdHasher
{
    public static function normalise(string $nationalId): string
    {
        return (string) preg_replace('/\D/', '', $nationalId);
    }

    public static function hash(string $nationalId): string
    {
        $key = (string) config('identity.nid_hash_key');

        if ($key === '') {
            throw new RuntimeException('NID_HASH_KEY is not set (D-117).');
        }

        return hash_hmac('sha256', self::normalise($nationalId), $key);
    }
}
