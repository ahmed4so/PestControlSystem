<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domain\CompanyRegistry\Support\CompanyEditLock;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\User;

/**
 * Company profile (03 §11.3): a company works on its own profile while it
 * is unlocked (D-073); staff with company-file access read it (D-177).
 */
class CompanyPolicy
{
    public function __construct(private readonly CompanyEditLock $lock) {}

    public function viewAny(User $user): bool
    {
        return $user->can('view applications');
    }

    public function view(User|CompanyUser $user, Company $company): bool
    {
        return $user instanceof CompanyUser
            ? $user->company_id === $company->id
            : $user->can('view applications');
    }

    public function update(User|CompanyUser $user, Company $company): bool
    {
        return $user instanceof CompanyUser
            && $user->company_id === $company->id
            && $this->lock->canEditProfile($company);
    }
}
