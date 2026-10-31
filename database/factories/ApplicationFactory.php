<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Applications\Enums\ApplicationType;
use App\Domain\Applications\Enums\LicenseCategory;
use App\Domain\Applications\Enums\Service;
use App\Models\Application;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Application>
 */
class ApplicationFactory extends Factory
{
    /**
     * A Company License draft, holding its open key from creation (D-193).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'service' => Service::CompanyLicense,
            'license_category' => LicenseCategory::Importer,
            'application_type' => ApplicationType::New,
            'status' => ApplicationStatus::Draft,
            'open_key' => Service::CompanyLicense->openKey(LicenseCategory::Importer),
        ];
    }

    public function category(LicenseCategory $category): static
    {
        return $this->state(fn (array $attributes) => [
            'license_category' => $category,
            'open_key' => Service::CompanyLicense->openKey($category),
        ]);
    }

    public function status(ApplicationStatus $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
            'open_key' => $status->isFinal() ? null : $attributes['open_key'],
        ]);
    }
}
