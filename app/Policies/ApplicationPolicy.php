<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domain\Applications\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\CompanyUser;
use App\Models\Inspection;
use App\Models\StageAssignment;
use App\Models\User;

/**
 * Applications (02 §4.6 access: company R/W on draft and returned, staff R).
 * Status changes are not authorized here: only WorkflowEngine::perform()
 * changes a status, with its own checks (D-096).
 */
class ApplicationPolicy
{
    public function viewAny(User|CompanyUser $user): bool
    {
        return $user instanceof CompanyUser
            ? $user->company_id !== null
            : $user->can('view applications');
    }

    public function view(User|CompanyUser $user, Application $application): bool
    {
        if ($user instanceof CompanyUser) {
            return $user->company_id === $application->company_id;
        }

        if ($user->can('view applications')) {
            return true;
        }

        return $user->can('view assigned applications') && self::isAssigned($user, $application);
    }

    /**
     * A Technical Reviewer assignment to the application, open or ended, or
     * an inspection of it assigned to the user (D-077).
     */
    public static function isAssigned(User $user, Application $application): bool
    {
        return StageAssignment::query()
            ->where('application_id', $application->id)
            ->where('assigned_user_id', $user->id)
            ->exists()
            || Inspection::query()
                ->where('application_id', $application->id)
                ->where('inspector_id', $user->id)
                ->exists();
    }

    public function create(User|CompanyUser $user): bool
    {
        return $user instanceof CompanyUser && $user->company_id !== null;
    }

    /**
     * Choosing premises and the technical person: own company, while the
     * application is a draft or returned (D-073).
     */
    public function update(User|CompanyUser $user, Application $application): bool
    {
        return $user instanceof CompanyUser
            && $user->company_id === $application->company_id
            && in_array($application->status, [ApplicationStatus::Draft, ApplicationStatus::Returned], true);
    }
}
