<?php

declare(strict_types=1);

namespace App\Domain\Applications\Support;

use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Applications\Enums\Service;
use App\Domain\CompanyRegistry\Contracts\SubmissionState;
use App\Models\Application;
use App\Models\Company;

/**
 * The real edit lock (D-073, 02 §6.4), replacing Phase 2's
 * NothingSubmittedYet:
 * - locked while any application is in process (submitted, not returned,
 *   not final), even if a license draft is also open (Proposed D-203);
 * - otherwise unlocked while a Company or Operator License application is
 *   in DRAFT or RETURNED (an Import Permit draft never unlocks, rule 2);
 * - otherwise locked once a license was issued, and unlocked before that
 *   (first set-up, or only rejected applications).
 * Until the Licensing module exists (Phase 4B), an ISSUED application
 * stands for an issued license.
 */
final class ApplicationSubmissionState implements SubmissionState
{
    private const OPEN_FOR_EDIT = [ApplicationStatus::Draft, ApplicationStatus::Returned];

    private const LICENSE_SERVICES = [Service::CompanyLicense, Service::OperatorLicense];

    public function profileIsUnlocked(Company $company): bool
    {
        $applications = Application::query()->withoutGlobalScope('company')
            ->where('company_id', $company->id)
            ->get(['id', 'service', 'status']);

        $inProcess = $applications->contains(fn (Application $application) => ! in_array($application->status, self::OPEN_FOR_EDIT, true)
            && ! $application->status->isFinal());

        if ($inProcess) {
            return false;
        }

        $licenseDraftOpen = $applications->contains(fn (Application $application) => in_array($application->service, self::LICENSE_SERVICES, true)
            && in_array($application->status, self::OPEN_FOR_EDIT, true));

        if ($licenseDraftOpen) {
            return true;
        }

        return ! $applications->contains(fn (Application $application) => in_array($application->service, self::LICENSE_SERVICES, true)
            && $application->status === ApplicationStatus::Issued);
    }
}
