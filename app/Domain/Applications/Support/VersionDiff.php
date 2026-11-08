<?php

declare(strict_types=1);

namespace App\Domain\Applications\Support;

/**
 * The differences between two version snapshots, computed when the page
 * opens (D-199). Fields are compared one by one; list entries that carry
 * an `id` (persons, premises, documents) are matched by that id, so a
 * reordered list is not reported as a change.
 */
final class VersionDiff
{
    /**
     * @param  array<string, mixed>  $old
     * @param  array<string, mixed>  $new
     * @return list<array{field: string, old: mixed, new: mixed}>
     */
    public static function between(array $old, array $new): array
    {
        $before = self::flatten(SnapshotPresenter::forStaff($old));
        $after = self::flatten(SnapshotPresenter::forStaff($new));
        $changes = [];

        foreach (array_unique([...array_keys($before), ...array_keys($after)]) as $field) {
            $was = $before[$field] ?? null;
            $is = $after[$field] ?? null;

            if ($was !== $is && $field !== 'schema_version') {
                $changes[] = ['field' => $field, 'old' => $was, 'new' => $is];
            }
        }

        return $changes;
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return array<string, mixed>
     */
    private static function flatten(array $data, string $prefix = ''): array
    {
        $flat = [];

        foreach ($data as $key => $value) {
            if (is_array($value) && array_is_list($value) && $value !== [] && is_array($value[0]) && array_key_exists('id', $value[0])) {
                foreach ($value as $entry) {
                    $flat += self::flatten((array) $entry, $prefix.$key.'['.($entry['id'] ?? '?').'].');
                }
            } elseif (is_array($value) && $value !== []) {
                $flat += self::flatten($value, $prefix.$key.'.');
            } else {
                $flat[$prefix.$key] = $value === [] ? null : $value;
            }
        }

        return $flat;
    }
}
