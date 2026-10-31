<?php

declare(strict_types=1);

namespace App\Domain\Applications\Snapshots;

use App\Models\ApplicationVersion;

/**
 * SHA-256 over canonical JSON (D-069, D-190): keys sorted at every level
 * (lists keep their order), UTF-8, unescaped slashes and Unicode. The same
 * data always gives the same bytes, so a hash can be checked later even
 * though MySQL reorders the keys of the stored JSON.
 */
final class SnapshotHasher
{
    /**
     * @param  array<array-key, mixed>  $snapshot
     */
    public function canonicalJson(array $snapshot): string
    {
        return json_encode(
            self::sortKeys($snapshot),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR,
        );
    }

    /**
     * @param  array<array-key, mixed>  $snapshot
     */
    public function hash(array $snapshot): string
    {
        return hash('sha256', $this->canonicalJson($snapshot));
    }

    /**
     * The stored hash still matches the stored snapshot.
     */
    public function verify(ApplicationVersion $version): bool
    {
        return hash_equals($version->snapshot_hash, $this->hash($version->snapshot));
    }

    private static function sortKeys(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        $value = array_map(self::sortKeys(...), $value);

        if (! array_is_list($value)) {
            ksort($value, SORT_STRING);
        }

        return $value;
    }
}
