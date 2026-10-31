<?php

declare(strict_types=1);

namespace App\Domain\Workflow\Exceptions;

use RuntimeException;

/**
 * A workflow definition failed validation; nothing was loaded (04 §13,
 * 04 §16 item 10).
 */
final class InvalidWorkflowDefinition extends RuntimeException
{
    /**
     * @param  list<string>  $errors
     */
    public function __construct(public readonly string $definitionCode, public readonly array $errors)
    {
        parent::__construct("Workflow definition [{$definitionCode}] is invalid:\n- ".implode("\n- ", $errors));
    }
}
