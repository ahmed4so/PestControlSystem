<?php

declare(strict_types=1);

namespace App\Domain\Workflow\Exceptions;

use RuntimeException;

/**
 * A transition was refused; nothing changed (04 §4). The reasons are
 * English keys, shown through __().
 */
final class TransitionRefused extends RuntimeException
{
    /**
     * @param  list<string>  $reasons
     */
    public function __construct(public readonly array $reasons)
    {
        parent::__construct(implode(' ', $reasons), 422);
    }

    public static function because(string ...$reasons): self
    {
        return new self(array_values($reasons));
    }
}
