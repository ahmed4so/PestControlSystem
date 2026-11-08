<?php

declare(strict_types=1);

namespace App\Domain\Inspection\Support;

use App\Domain\Identity\Enums\AccountStatus;
use App\Domain\Notifications\Actions\NotifyWorkflowEvent;
use App\Domain\Notifications\Actions\SendNotification;
use App\Domain\Notifications\Enums\NotificationCode;
use App\Models\Application;
use App\Models\CompanyUser;
use App\Models\Inspection;
use App\Models\User;

/**
 * INSPECTION_SCHEDULED and INSPECTION_RESCHEDULED (07 §3.3): one email per
 * inspection to the company and to its Inspector, with the premises and
 * the planned date. Never to the staff member who acted (D-127).
 */
final class InspectionNotifications
{
    public function __construct(
        private readonly SendNotification $send,
        private readonly NotifyWorkflowEvent $notifier,
    ) {}

    /**
     * The newest round of an application.
     */
    public function scheduled(Application $application, ?User $actor): void
    {
        $round = Inspection::query()->where('application_id', $application->id)->max('round');

        Inspection::query()
            ->where('application_id', $application->id)
            ->where('round', $round)
            ->open()
            ->with(['premises', 'inspector'])
            ->get()
            ->each(fn (Inspection $inspection) => $this->about($inspection, NotificationCode::InspectionScheduled, $application, $actor));
    }

    public function rescheduled(Inspection $inspection, ?User $actor): void
    {
        $inspection->loadMissing(['premises', 'inspector']);
        $this->about($inspection, NotificationCode::InspectionRescheduled, Application::query()->withoutGlobalScope('company')->findOrFail($inspection->application_id), $actor);
    }

    private function about(Inspection $inspection, NotificationCode $code, Application $application, ?User $actor): void
    {
        $data = [
            ...$this->notifier->baseData($application),
            'premises' => (string) $inspection->premises?->label,
            'date' => $inspection->planned_date->format('Y-m-d'),
        ];

        $accounts = CompanyUser::query()
            ->where('company_id', $application->company_id)
            ->where('status', AccountStatus::Active)
            ->get();

        foreach ($accounts as $account) {
            ($this->send)($account, $code, [...$data, 'url' => $data['company_url']], related: $inspection, reference: $application->reference);
        }

        $inspector = $inspection->inspector;

        if ($inspector instanceof User && $inspector->status === AccountStatus::Active && ! $inspector->is($actor)) {
            ($this->send)($inspector, $code, [...$data, 'url' => route('admin.inspections.show', $inspection)], related: $inspection, reference: $application->reference);
        }
    }
}
