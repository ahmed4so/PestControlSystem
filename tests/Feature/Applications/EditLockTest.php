<?php

declare(strict_types=1);

use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Applications\Enums\LicenseCategory;
use App\Domain\Applications\Enums\Service;
use App\Domain\CompanyRegistry\Enums\DocumentType;
use App\Domain\CompanyRegistry\Support\CompanyEditLock;
use App\Models\Application;
use App\Models\Company;
use Illuminate\Http\UploadedFile;

/*
 * The company data edit lock follows the applications (D-073, 02 §6.4,
 * Proposed D-203), enforced by the policies on every update endpoint.
 */

function lockFor(Company $company): bool
{
    return ! app(CompanyEditLock::class)->canEditProfile($company);
}

function applicationOf(Company $company, ApplicationStatus $status, Service $service = Service::CompanyLicense, LicenseCategory $category = LicenseCategory::Importer): Application
{
    $application = Application::factory()->category($category)->status($status)->create(['company_id' => $company->id]);

    if ($service !== Service::CompanyLicense) {
        $application->forceFill([
            'service' => $service,
            'license_category' => null,
            'open_key' => $service->openKey(null),
        ])->save();
    }

    return $application;
}

it('leaves the profile editable before any submission', function () {
    expect(lockFor(Company::factory()->create()))->toBeFalse();
});

it('keeps the profile editable while a license application is a draft or returned', function (ApplicationStatus $status) {
    $company = Company::factory()->create();
    applicationOf($company, $status);

    expect(lockFor($company))->toBeFalse();
})->with([ApplicationStatus::Draft, ApplicationStatus::Returned]);

it('locks the profile while any application is in process', function (ApplicationStatus $status) {
    $company = Company::factory()->create();
    applicationOf($company, $status);

    expect(lockFor($company))->toBeTrue();
})->with([
    ApplicationStatus::Submitted, ApplicationStatus::AdminReview, ApplicationStatus::TechnicalDocReview,
    ApplicationStatus::InspectionScheduled, ApplicationStatus::ConsolidatedReview, ApplicationStatus::DirectorReview,
    ApplicationStatus::AwaitingPayment, ApplicationStatus::DgDecision,
]);

it('keeps the lock while another application is in process, even with a draft open (D-203)', function () {
    $company = Company::factory()->create();
    applicationOf($company, ApplicationStatus::Returned, category: LicenseCategory::Importer);
    applicationOf($company, ApplicationStatus::AdminReview, category: LicenseCategory::Retailer);

    expect(lockFor($company))->toBeTrue();
});

it('locks the profile once a license is issued, until a new license draft opens', function () {
    $company = Company::factory()->create();
    applicationOf($company, ApplicationStatus::Issued, category: LicenseCategory::Importer);
    expect(lockFor($company))->toBeTrue();

    applicationOf($company, ApplicationStatus::Draft, category: LicenseCategory::Retailer);
    expect(lockFor($company))->toBeFalse();
});

it('does not let an Import Permit draft unlock the profile (02 §6.4 rule 2)', function () {
    $company = Company::factory()->create();
    applicationOf($company, ApplicationStatus::Issued, category: LicenseCategory::Importer);
    applicationOf($company, ApplicationStatus::Draft, Service::ImportPermit);

    expect(lockFor($company))->toBeTrue();
});

it('lets an Operator License draft unlock the profile like a Company License draft', function () {
    $company = Company::factory()->create();
    applicationOf($company, ApplicationStatus::Issued);
    applicationOf($company, ApplicationStatus::Returned, Service::OperatorLicense);

    expect(lockFor($company))->toBeFalse();
});

it('refuses profile and document changes through the portal while locked', function () {
    $account = companyUser();
    $company = Company::query()->findOrFail($account->company_id);
    applicationOf($company, ApplicationStatus::AdminReview);

    $this->actingAs($account, 'company')->put(route('portal.company.update'), [
        'name' => 'Changed Ltd', 'business_registration_no' => $company->business_registration_no,
        'tax_compliance_no' => 'TX-1', 'email' => 'info@example.com', 'phone' => '+252612222222',
    ])->assertForbidden();

    $this->actingAs($account, 'company')->post(route('portal.documents.store'), [
        'document_type' => DocumentType::CompanyProfile->value,
        'file' => UploadedFile::fake()->createWithContent('profile.pdf', "%PDF-1.4\n%%EOF"),
    ])->assertForbidden();

    expect(Company::query()->withoutGlobalScopes()->findOrFail($company->id)->name)->not->toBe('Changed Ltd');
});
