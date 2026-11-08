<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Inspection\Enums\InspectionStatus;
use App\Models\Application;
use App\Models\ApplicationVersion;
use App\Models\Inspection;
use App\Models\Premises;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inspection>
 */
class InspectionFactory extends Factory
{
    /**
     * A scheduled inspection, planned for tomorrow. Pass `application_id`,
     * `application_version_id` and `premises_id` of a real submission.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'application_version_id' => fn (array $attributes) => ApplicationVersion::query()
                ->where('application_id', $attributes['application_id'])->value('id'),
            'premises_id' => Premises::factory(),
            'inspector_id' => User::factory(),
            'scheduled_by' => User::factory(),
            'round' => 1,
            'planned_date' => now()->addDay()->toDateString(),
            'status' => InspectionStatus::Scheduled,
        ];
    }
}
