<?php

declare(strict_types=1);

namespace App\Domain\Workflow\Engine;

/**
 * The outcome of a guard: passed, or failed with reasons the user can read
 * (English keys for __(), 04 §16 item 3).
 */
final readonly class GuardResult
{
    /**
     * @param  list<string>  $reasons
     */
    private function __construct(public bool $passed, public array $reasons) {}

    public static function pass(): self
    {
        return new self(true, []);
    }

    public static function fail(string ...$reasons): self
    {
        return new self(false, array_values($reasons));
    }

    /**
     * @param  list<string>  $reasons
     */
    public static function failIfAny(array $reasons): self
    {
        return $reasons === [] ? self::pass() : new self(false, $reasons);
    }
}
