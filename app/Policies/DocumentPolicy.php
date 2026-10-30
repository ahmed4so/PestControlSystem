<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domain\CompanyRegistry\Enums\DocumentType;
use App\Domain\CompanyRegistry\Support\CompanyEditLock;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Document;
use App\Models\User;

/**
 * Documents follow the profile lock, except renewed commerce and tax
 * certificates, which can be uploaded at any time (D-073).
 */
class DocumentPolicy
{
    public function __construct(private readonly CompanyEditLock $lock) {}

    public function create(User|CompanyUser $user, Company $company, DocumentType $type): bool
    {
        return $user instanceof CompanyUser
            && $user->company_id === $company->id
            && $this->lock->canUploadDocument($company, $type);
    }

    public function view(User|CompanyUser $user, Document $document): bool
    {
        return $user instanceof CompanyUser
            ? $user->company_id === $document->company_id
            : $user->can('view applications');
    }
}
