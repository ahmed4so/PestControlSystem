<?php

declare(strict_types=1);

namespace App\Domain\Workflow\Contracts;

use App\Domain\Workflow\Engine\TransitionContext;

/**
 * A named step run inside the transition's database transaction (04 §12).
 * Referenced in definitions as `Name` or `Name:param`.
 */
interface Effect
{
    public function apply(TransitionContext $context, ?string $param): void;

    /**
     * Validation rules for the input this effect needs (checked before any
     * guard or effect runs).
     *
     * @return array<string, mixed>
     */
    public function payloadRules(?string $param): array;
}
