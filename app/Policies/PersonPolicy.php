<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domain\CompanyRegistry\Support\CompanyEditLock;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Person;
use App\Models\User;

/**
 * Persons follow the profile lock (D-073); a released person is read-only
 * (D-066).
 */
class PersonPolicy
{
    public function __construct(private readonly CompanyEditLock $lock) {}

    public function create(User|CompanyUser $user, Company $company): bool
    {
        return $user instanceof CompanyUser
            && $user->company_id === $company->id
            && $this->lock->canEditProfile($company);
    }

    public function update(User|CompanyUser $user, Person $person): bool
    {
        return $user instanceof CompanyUser
            && $user->company_id === $person->company_id
            && ! $person->isReleased()
            && $this->lock->canEditProfile($person->company);
    }
}
