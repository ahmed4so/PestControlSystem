<?php

declare(strict_types=1);

namespace App\Domain\Audit\Concerns;

use App\Domain\Audit\Pipes\MaskSensitiveAttributes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Writes every create, update and delete of the model to the audit log with
 * before and after values (D-055, 03 §13). Hidden attributes (passwords,
 * tokens, 2FA secrets) are never logged; attributes returned by
 * auditMaskedAttributes() are masked (D-091, 08 §4.2 rule 10).
 */
trait Audited
{
    use LogsActivity;

    public static function bootAudited(): void
    {
        static::addLogChange(new MaskSensitiveAttributes);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('audit')
            ->logFillable()
            ->logExcept($this->getHidden())
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * @return list<string>
     */
    public function auditMaskedAttributes(): array
    {
        return [];
    }
}
