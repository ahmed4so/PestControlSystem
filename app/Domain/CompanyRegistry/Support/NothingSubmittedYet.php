<?php

declare(strict_types=1);

namespace App\Domain\CompanyRegistry\Support;

use App\Domain\CompanyRegistry\Contracts\SubmissionState;
use App\Models\Company;

/**
 * Phase 2 submission state: no applications exist yet, so every profile
 * is before its first submission and therefore editable (D-073). Replaced
 * in Phase 4.
 */
final class NothingSubmittedYet implements SubmissionState
{
    public function profileIsUnlocked(Company $company): bool
    {
        return true;
    }
}
