<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domain\CompanyRegistry\Support\CompanyEditLock;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Premises;
use App\Models\User;

/**
 * Premises, their checklist and photos follow the profile lock (D-073).
 */
class PremisesPolicy
{
    public function __construct(private readonly CompanyEditLock $lock) {}

    public function create(User|CompanyUser $user, Company $company): bool
    {
        return $user instanceof CompanyUser
            && $user->company_id === $company->id
            && $this->lock->canEditProfile($company);
    }

    public function update(User|CompanyUser $user, Premises $premises): bool
    {
        return $user instanceof CompanyUser
            && $user->company_id === $premises->company_id
            && $this->lock->canEditProfile($premises->company);
    }
}
