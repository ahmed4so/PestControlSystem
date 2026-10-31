<?php

declare(strict_types=1);

namespace App\Domain\Workflow\Concerns;

use LogicException;

/**
 * Records that are written once and never changed or removed: submission
 * versions and the workflow history (02-data-model §4.6–§4.7, D-042,
 * D-187).
 */
trait Immutable
{
    public static function bootImmutable(): void
    {
        static::updating(fn () => throw new LogicException(class_basename(static::class).' records cannot be changed.'));
        static::deleting(fn () => throw new LogicException(class_basename(static::class).' records cannot be deleted.'));
    }
}
