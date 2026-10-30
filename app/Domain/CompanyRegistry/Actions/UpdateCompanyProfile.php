<?php

declare(strict_types=1);

namespace App\Domain\CompanyRegistry\Actions;

use App\Domain\CompanyRegistry\Support\ProfileCompleteness;
use App\Models\Company;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Saves the company profile; edits are saved immediately (D-073) and the
 * completeness is recalculated (D-178).
 */
final class UpdateCompanyProfile
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __invoke(Company $company, array $data): void
    {
        try {
            DB::transaction(function () use ($company, $data): void {
                $company->update($data);
                ProfileCompleteness::refresh($company);
            });
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['business_registration_no' => __(CreateCompany::DUPLICATE_MESSAGE)]);
        }
    }
}
