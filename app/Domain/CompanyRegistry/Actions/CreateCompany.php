<?php

declare(strict_types=1);

namespace App\Domain\CompanyRegistry\Actions;

use App\Domain\Audit\Support\AuditLog;
use App\Models\Company;
use App\Models\CompanyUser;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Creates the company for an account (02 §4.4, §6.2): one company per
 * account, and a business registration number can belong to one company
 * only (D-182).
 */
final class CreateCompany
{
    public const DUPLICATE_MESSAGE = 'This business registration number is already registered. Please contact SARIS.';

    /**
     * @param  array{name: string, business_registration_no: string}  $data
     */
    public function __invoke(CompanyUser $account, array $data): Company
    {
        try {
            return DB::transaction(function () use ($account, $data): Company {
                $account = CompanyUser::query()->lockForUpdate()->findOrFail($account->id);

                if ($account->company_id !== null) {
                    return Company::query()->withoutGlobalScopes()->findOrFail($account->company_id);
                }

                if (Company::query()->withoutGlobalScopes()->withTrashed()->where('business_registration_no', $data['business_registration_no'])->exists()) {
                    throw $this->duplicate();
                }

                $company = Company::query()->create($data);

                $account->forceFill(['company_id' => $company->id])->saveQuietly();

                AuditLog::record('company_created', 'Company profile created', $company, $account);

                return $company;
            });
        } catch (UniqueConstraintViolationException) {
            throw $this->duplicate();
        }
    }

    private function duplicate(): ValidationException
    {
        return ValidationException::withMessages(['business_registration_no' => __(self::DUPLICATE_MESSAGE)]);
    }
}
