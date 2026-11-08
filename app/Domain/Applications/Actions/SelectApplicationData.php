<?php

declare(strict_types=1);

namespace App\Domain\Applications\Actions;

use App\Domain\Applications\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\Person;
use App\Models\Premises;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use LogicException;

/**
 * Links a draft (or a returned application) to the company's live
 * premises and technical person (02 §4.6: the links point to live data;
 * the submitted values live in the version snapshot). Changes are saved
 * immediately (H6) and touch the application, so it is not "untouched"
 * for the stale-draft rule (D-193, D-194).
 */
final class SelectApplicationData
{
    /**
     * @param  list<int>  $premisesIds
     */
    public function premises(Application $application, array $premisesIds): void
    {
        $this->ensureEditable($application);

        $owned = Premises::query()->withoutGlobalScope('company')
            ->where('company_id', $application->company_id)
            ->whereIn('id', $premisesIds)
            ->pluck('id')
            ->all();

        if (count($owned) !== count(array_unique($premisesIds))) {
            throw ValidationException::withMessages(['premises' => __('Select only your own premises.')]);
        }

        DB::transaction(function () use ($application, $owned): void {
            $application->premises()->sync($owned);
            $application->touch();
        });
    }

    public function technicalPerson(Application $application, ?int $personId): void
    {
        $this->ensureEditable($application);

        if ($personId !== null) {
            $person = Person::query()->withoutGlobalScope('company')
                ->where('company_id', $application->company_id)
                ->whereNull('released_at')
                ->find($personId);

            if ($person === null) {
                throw ValidationException::withMessages(['person_id' => __('Select one technical person.')]);
            }
        }

        DB::transaction(function () use ($application, $personId): void {
            $application->technicalPersons()->sync($personId === null ? [] : [$personId]);
            $application->touch();
        });
    }

    private function ensureEditable(Application $application): void
    {
        if (! in_array($application->status, [ApplicationStatus::Draft, ApplicationStatus::Returned], true)) {
            throw new LogicException('Only a draft or a returned application can be changed (D-073).');
        }
    }
}
