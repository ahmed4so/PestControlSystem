<?php

declare(strict_types=1);

namespace App\Domain\Applications\Actions;

use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Applications\Enums\ApplicationType;
use App\Domain\Applications\Enums\LicenseCategory;
use App\Domain\Applications\Enums\Service;
use App\Domain\Workflow\Guards\NoOtherOpenApplication;
use App\Models\Application;
use App\Models\Company;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Validation\ValidationException;

/**
 * Starts a Company License draft (Form 1, D-014). The draft holds its open
 * key from creation, so a second open application of the same category is
 * refused by the database (D-044, D-193). Phase 4A offers New only;
 * renewal and amendment need licenses (Phase 4B).
 */
final class CreateDraft
{
    public function __invoke(Company $company, LicenseCategory $category): Application
    {
        $application = new Application([
            'service' => Service::CompanyLicense,
            'license_category' => $category,
            'application_type' => ApplicationType::New,
        ]);
        $application->company()->associate($company);
        $application->status = ApplicationStatus::Draft;
        $application->open_key = Service::CompanyLicense->openKey($category);

        try {
            $application->save();
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['license_category' => __(NoOtherOpenApplication::REASON)]);
        }

        return $application;
    }
}
