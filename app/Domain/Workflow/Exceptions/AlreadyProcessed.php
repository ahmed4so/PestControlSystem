<?php

declare(strict_types=1);

namespace App\Domain\Workflow\Exceptions;

use RuntimeException;

/**
 * The application is no longer in the status the user saw: someone else
 * acted first (04 §4 rule 2, D-096). Shown as HTTP 409.
 */
final class AlreadyProcessed extends RuntimeException
{
    public const MESSAGE = 'This application has already been processed. Reload the page to see its current status.';

    public function __construct()
    {
        parent::__construct(self::MESSAGE, 409);
    }
}
