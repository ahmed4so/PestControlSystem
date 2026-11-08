<?php

declare(strict_types=1);

namespace App\Domain\Applications\Queries;

use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Identity\Enums\StaffRole;
use App\Domain\Workflow\Checks\DelegationCheck;
use App\Domain\Workflow\Enums\ActorRole;
use App\Models\Application;
use App\Models\User;
use App\Models\WorkflowAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Staff work queues (04 §15, D-105):
 * - My work: applications whose current stage's role the user holds (or
 *   holds for the DG through an active delegation, D-027), plus their open
 *   Technical Reviewer assignments;
 * - By stage: count and oldest age per stage;
 * - Waiting on company: RETURNED and AWAITING_PAYMENT, with days waiting;
 * - Flags: late renewals, restricted ingredients, amendment-required
 *   companies (flagged permits come with the Import Permit phase).
 * The stage role comes from each application's own template version.
 */
final class WorkQueues
{
    public const PER_PAGE = 25;

    /** Stages the "by stage" view counts, for the Registrar, Director and DG. */
    public const BY_STAGE_ROLES = [StaffRole::PesticideRegistrar, StaffRole::DepartmentalDirector, StaffRole::DirectorGeneral];

    /**
     * @return list<string> actor codes the user may act as now
     */
    public static function actorCodes(User $user): array
    {
        $codes = [];

        foreach (StaffRole::cases() as $role) {
            if ($role->code() !== null && $user->hasRole($role->value)) {
                $codes[] = $role->code();
            }
        }

        if (! in_array(ActorRole::DirectorGeneral->value, $codes, true) && DelegationCheck::activeDelegation($user) !== null) {
            $codes[] = ActorRole::DirectorGeneral->value;
        }

        return $codes;
    }

    public function myWork(User $user): LengthAwarePaginator
    {
        $codes = self::actorCodes($user);

        // Technical Reviewers and Inspectors only see what they are assigned
        // to; their stage role alone does not make work theirs (04 §10).
        $byRole = array_values(array_diff($codes, [ActorRole::TechnicalReviewer->value, ActorRole::Inspector->value]));

        return $this->base()
            ->where(function (Builder $query) use ($byRole, $user): void {
                $query->whereIn('workflow_stages.actor_role', $byRole === [] ? ['-'] : $byRole)
                    ->orWhereExists(fn ($assigned) => $assigned->select(DB::raw(1))
                        ->from('stage_assignments')
                        ->whereColumn('stage_assignments.application_id', 'applications.id')
                        ->whereColumn('stage_assignments.stage', 'applications.status')
                        ->where('stage_assignments.assigned_user_id', $user->id)
                        ->whereNull('stage_assignments.ended_at'));
            })
            ->orderBy('entered_at')
            ->paginate(self::PER_PAGE)
            ->withQueryString()
            ->through(fn (Application $application) => self::row($application));
    }

    /**
     * @return list<array{status: string, stage: string, role: string, count: int, oldest_days: int}>
     */
    public function byStage(): array
    {
        $codes = array_map(fn (StaffRole $role) => (string) $role->code(), self::BY_STAGE_ROLES);

        return $this->base()
            ->whereIn('workflow_stages.actor_role', $codes)
            ->get()
            ->groupBy(fn (Application $application) => $application->status->value)
            ->map(function ($applications, string $status) {
                /** @var Application $first */
                $first = $applications->first();

                return [
                    'status' => $status,
                    'stage' => (string) $first->getAttribute('stage_name'),
                    'role' => (string) $first->getAttribute('stage_actor'),
                    'count' => $applications->count(),
                    'oldest_days' => (int) $applications->max(fn (Application $application) => self::days($application)),
                ];
            })
            ->sortBy('status')
            ->values()
            ->all();
    }

    public function waitingOnCompany(): LengthAwarePaginator
    {
        return $this->base()
            ->whereIn('applications.status', [ApplicationStatus::Returned->value, ApplicationStatus::AwaitingPayment->value])
            ->orderBy('entered_at')
            ->paginate(self::PER_PAGE)
            ->withQueryString()
            ->through(fn (Application $application) => self::row($application));
    }

    public function flags(): LengthAwarePaginator
    {
        return $this->base()
            ->where(fn (Builder $query) => $query
                ->where('applications.is_late_renewal', true)
                ->orWhere('applications.has_restricted_ingredient', true)
                ->orWhereNotNull('companies.amendment_required_at'))
            ->orderBy('entered_at')
            ->paginate(self::PER_PAGE)
            ->withQueryString()
            ->through(fn (Application $application) => [
                ...self::row($application),
                'flags' => array_values(array_filter([
                    $application->is_late_renewal ? 'Late renewal' : null,
                    $application->has_restricted_ingredient ? 'Restricted ingredient' : null,
                    $application->getAttribute('amendment_required_at') !== null ? 'Amendment required' : null,
                ])),
            ]);
    }

    /**
     * Submitted applications that are not final, with their stage and the
     * time they entered it.
     *
     * @return Builder<Application>
     */
    private function base(): Builder
    {
        $entered = WorkflowAction::query()
            ->selectRaw('max(created_at)')
            ->whereColumn('workflow_actions.application_id', 'applications.id')
            ->whereColumn('workflow_actions.to_status', 'applications.status');

        return Application::query()->withoutGlobalScope('company')
            ->join('workflow_stages', function ($join): void {
                $join->on('workflow_stages.workflow_template_id', '=', 'applications.workflow_template_id')
                    ->on('workflow_stages.code', '=', 'applications.status')
                    ->whereNull('workflow_stages.deleted_at');
            })
            ->join('companies', 'companies.id', '=', 'applications.company_id')
            ->whereNotIn('applications.status', [ApplicationStatus::Draft->value, ApplicationStatus::Issued->value, ApplicationStatus::Rejected->value])
            ->select([
                'applications.*',
                (app()->getLocale() === 'so' ? 'workflow_stages.name_so' : 'workflow_stages.name_en').' as stage_name',
                'workflow_stages.actor_role as stage_actor',
                'companies.name as company_name',
                'companies.amendment_required_at as amendment_required_at',
            ])
            ->addSelect(['entered_at' => $entered]);
    }

    /**
     * @return array<string, mixed>
     */
    private static function row(Application $application): array
    {
        return [
            'id' => $application->id,
            'reference' => $application->reference,
            'company' => (string) $application->getAttribute('company_name'),
            'service' => $application->service->label(),
            'category' => $application->license_category?->label(),
            'status' => $application->status->value,
            'stage' => (string) $application->getAttribute('stage_name'),
            'days' => self::days($application),
        ];
    }

    private static function days(Application $application): int
    {
        $entered = $application->getAttribute('entered_at');

        return $entered === null ? 0 : (int) now()->diffInDays(Carbon::parse((string) $entered), absolute: true);
    }
}
