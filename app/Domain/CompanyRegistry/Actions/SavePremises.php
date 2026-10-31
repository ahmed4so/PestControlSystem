<?php

declare(strict_types=1);

namespace App\Domain\CompanyRegistry\Actions;

use App\Models\Company;
use App\Models\Premises;

/**
 * Creates or updates a premises (02 §4.4). The facility checklist is
 * recorded by the Inspector, not the company (D-192).
 */
final class SavePremises
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __invoke(Company $company, array $data, ?Premises $premises = null): Premises
    {
        $premises ??= new Premises;
        $premises->fill($data);
        $premises->company()->associate($company);
        $premises->save();

        return $premises;
    }
}
