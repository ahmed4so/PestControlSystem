<?php

declare(strict_types=1);

namespace App\Domain\CompanyRegistry\Actions;

use App\Domain\Audit\Support\AuditLog;
use App\Domain\CompanyRegistry\Support\ProfileCompleteness;
use App\Domain\Notifications\Actions\SendNotification;
use App\Domain\Notifications\Enums\NotificationCode;
use App\Models\Company;
use App\Models\Person;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Releases a national ID from its company after SARIS has verified the
 * move (D-066, D-095): the ID becomes free for another company, the old
 * company is marked amendment required and is told.
 */
final class ReleaseNationalId
{
    public function __construct(private readonly SendNotification $notify) {}

    public function __invoke(Person $person, User $releasedBy, string $reason): void
    {
        DB::transaction(function () use ($person, $releasedBy, $reason): void {
            $person->forceFill([
                'released_at' => now(),
                'released_by' => $releasedBy->id,
                'release_reason' => $reason,
            ])->save();

            $company = Company::query()->withoutGlobalScopes()->findOrFail($person->company_id);
            $company->forceFill(['amendment_required_at' => now()])->save();
            ProfileCompleteness::refresh($company);

            AuditLog::record('national_id_released', 'National ID released to another company', $person, $releasedBy, [
                'company_id' => $company->id,
                'reason' => $reason,
            ]);

            if ($company->account !== null) {
                ($this->notify)($company->account, NotificationCode::NationalIdReleased, [
                    'person' => $person->full_name,
                    'url' => route('portal.company.edit'),
                ], related: $company);
            }
        });
    }
}
