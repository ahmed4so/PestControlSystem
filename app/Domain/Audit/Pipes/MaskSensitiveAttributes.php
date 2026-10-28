<?php

declare(strict_types=1);

namespace App\Domain\Audit\Pipes;

use App\Domain\Audit\Support\Mask;
use Closure;
use Spatie\Activitylog\Contracts\LoggablePipe;
use Spatie\Activitylog\EventLogBag;

/**
 * Replaces the model's masked attributes with "****1234" in both the new and
 * the old values of an audit entry.
 */
class MaskSensitiveAttributes implements LoggablePipe
{
    public function handle(EventLogBag $event, Closure $next): EventLogBag
    {
        $masked = method_exists($event->model, 'auditMaskedAttributes') ? $event->model->auditMaskedAttributes() : [];

        foreach (['attributes', 'old'] as $section) {
            foreach ($masked as $attribute) {
                if (array_key_exists($attribute, $event->changes[$section] ?? [])) {
                    $event->changes[$section][$attribute] = Mask::value($event->changes[$section][$attribute]);
                }
            }
        }

        return $next($event);
    }
}
