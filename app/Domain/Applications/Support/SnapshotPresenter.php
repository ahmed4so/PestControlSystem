<?php

declare(strict_types=1);

namespace App\Domain\Applications\Support;

use App\Domain\Audit\Support\Mask;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

/**
 * A version snapshot as staff see it (02 §6.3): the encrypted national IDs
 * become masked values (D-181), and the files it references are listed so
 * that only those can be opened from the application file.
 */
final class SnapshotPresenter
{
    /**
     * @param  array<string, mixed>  $snapshot
     * @return array<string, mixed>
     */
    public static function forStaff(array $snapshot): array
    {
        return self::mask($snapshot);
    }

    /**
     * Every `stored_file_id` in a snapshot.
     *
     * @param  array<array-key, mixed>  $snapshot
     * @return list<int>
     */
    public static function storedFileIds(array $snapshot): array
    {
        $ids = [];

        array_walk_recursive($snapshot, function (mixed $value, int|string $key) use (&$ids): void {
            if ($key === 'stored_file_id' && is_int($value)) {
                $ids[] = $value;
            }
        });

        return array_values(array_unique($ids));
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return array<array-key, mixed>
     */
    private static function mask(array $data): array
    {
        foreach ($data as $key => $value) {
            if ($key === 'national_id_encrypted') {
                unset($data[$key]);
                $data['national_id'] = self::maskedId($value);
            } elseif (is_array($value)) {
                $data[$key] = self::mask($value);
            }
        }

        return $data;
    }

    private static function maskedId(mixed $encrypted): ?string
    {
        if (! is_string($encrypted) || $encrypted === '') {
            return null;
        }

        try {
            return (string) Mask::value(Crypt::decryptString($encrypted));
        } catch (DecryptException) {
            return '••••';
        }
    }
}
