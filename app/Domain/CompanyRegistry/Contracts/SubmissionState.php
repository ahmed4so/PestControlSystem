<?php

declare(strict_types=1);

namespace App\Domain\CompanyRegistry\Contracts;

use App\Models\Company;

/**
 * What the applications say about a company's editing rights (D-073).
 * Phase 2 has no applications; Phase 4 binds the real implementation.
 */
interface SubmissionState
{
    /**
     * True when profile, persons and premises may change: before the first
     * submission, or while a Company or Operator License application is in
     * DRAFT or RETURNED (D-073). Import Permit drafts do not unlock.
     */
    public function profileIsUnlocked(Company $company): bool;
}
