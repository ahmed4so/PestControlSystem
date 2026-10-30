<?php

declare(strict_types=1);

namespace App\Domain\CompanyRegistry\Support;

use App\Domain\CompanyRegistry\Contracts\SubmissionState;
use App\Domain\CompanyRegistry\Enums\DocumentType;
use App\Models\Company;

/**
 * The company data edit lock (D-073, 03 §11.3), enforced in the backend
 * through the policies.
 */
final class CompanyEditLock
{
    public function __construct(private readonly SubmissionState $submissions) {}

    /**
     * Profile, persons, premises and their documents.
     */
    public function canEditProfile(Company $company): bool
    {
        return $this->submissions->profileIsUnlocked($company);
    }

    /**
     * Renewed commerce and tax certificates can be uploaded at any time
     * (D-073); other documents follow the profile lock.
     */
    public function canUploadDocument(Company $company, DocumentType $type): bool
    {
        return $type->isCertificate() || $this->canEditProfile($company);
    }
}
