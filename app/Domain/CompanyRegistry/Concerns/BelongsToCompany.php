<?php

declare(strict_types=1);

namespace App\Domain\CompanyRegistry\Concerns;

use App\Domain\Identity\Enums\AuthArea;
use App\Models\Company;
use App\Models\CompanyUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Company-owned records (03 §11.3). On the Company Portal every query is
 * limited to the logged-in account's company, so another company's ID
 * gives 404 through route model binding. Staff queries are not limited;
 * staff are read-only by policy.
 *
 * @property int $company_id
 */
trait BelongsToCompany
{
    public static function bootBelongsToCompany(): void
    {
        static::addGlobalScope('company', function (Builder $query): void {
            if (AuthArea::current() !== AuthArea::Company) {
                return;
            }

            $account = auth('company')->user();
            $companyId = $account instanceof CompanyUser ? $account->company_id : null;

            $query->where($query->qualifyColumn('company_id'), $companyId ?? 0);
        });
    }

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
