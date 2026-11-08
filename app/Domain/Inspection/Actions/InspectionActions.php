<?php

declare(strict_types=1);

namespace App\Domain\Inspection\Actions;

use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Audit\Support\AuditLog;
use App\Domain\Documents\Services\SafeUploads;
use App\Domain\Identity\Enums\StaffRole;
use App\Domain\Inspection\Enums\InspectionItemResult;
use App\Domain\Inspection\Enums\MediaType;
use App\Domain\Inspection\Support\InspectionChecklist;
use App\Domain\Inspection\Support\InspectionNotifications;
use App\Domain\Workflow\Exceptions\TransitionRefused;
use App\Models\Application;
use App\Models\Inspection;
use App\Models\InspectionItem;
use App\Models\InspectionMedia;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * What happens on an inspection without a workflow transition (D-077,
 * D-136, D-196):
 * - the Inspector fills in the report of their visited inspection, saved
 *   as they go, until it is signed (T11 locks it);
 * - the Inspector adds photos and an optional video;
 * - while the application is at INSPECTION_REPORT, the visit to another
 *   premises and the rescheduling of an inspection not yet visited are
 *   recorded here, audited; at INSPECTION_SCHEDULED they are T10 and T9.
 */
final class InspectionActions
{
    public const REASON_NOT_YOURS = 'This inspection is not assigned to you.';

    public const REASON_LOCKED = 'This inspection report is signed and cannot change.';

    public const REASON_NOT_VISITED = 'Record the inspection visit first.';

    public const REASON_STAGE = 'Use the application actions at this stage.';

    public const REASON_VISITED = 'This inspection was already visited, so its date cannot change.';

    public const REASON_TOO_MANY_PHOTOS = 'This checklist item already has the maximum number of photos.';

    public const REASON_REGISTRAR = 'Only the Registrar can reschedule an inspection.';

    public function __construct(
        private readonly SafeUploads $uploads,
        private readonly InspectionNotifications $notifications,
    ) {}

    /**
     * @param  array{items?: array<string, array{result?: string|null, note?: string|null}>, observed_facts?: string|null, recommendations?: string|null, deficiencies?: string|null, overall_result?: string|null, gps_lat?: string|float|null, gps_lng?: string|float|null, gps_unavailable_reason?: string|null}  $report
     */
    public function saveReport(Inspection $inspection, User $inspector, array $report): void
    {
        $this->ensureEditableBy($inspection, $inspector);
        $this->ensureVisited($inspection);

        $application = $this->application($inspection);
        $checklist = InspectionChecklist::items($application->service);

        DB::transaction(function () use ($inspection, $report, $checklist): void {
            $inspection->fill(array_intersect_key($report, array_flip($inspection->getFillable())))->save();

            foreach ((array) ($report['items'] ?? []) as $code => $answer) {
                if (! array_key_exists($code, $checklist) || blank($answer['result'] ?? null)) {
                    continue;
                }

                $item = InspectionItem::query()->firstOrNew(['inspection_id' => $inspection->id, 'item_code' => $code]);
                $item->inspection_id = $inspection->id;
                $item->fill([
                    'result' => InspectionItemResult::from((string) $answer['result']),
                    'note' => $answer['note'] ?? null,
                ])->save();
            }
        });
    }

    public function addMedia(Inspection $inspection, User $inspector, UploadedFile $file, MediaType $type, ?string $itemCode, ?float $lat, ?float $lng): InspectionMedia
    {
        $this->ensureEditableBy($inspection, $inspector);
        $this->ensureVisited($inspection);

        $item = $itemCode === null ? null : InspectionItem::query()
            ->where('inspection_id', $inspection->id)
            ->where('item_code', $itemCode)
            ->first();

        if ($item !== null && $type === MediaType::Photo) {
            $photos = InspectionMedia::query()->where('inspection_item_id', $item->id)->where('media_type', MediaType::Photo)->count();

            if ($photos >= InspectionChecklist::maxPhotosPerItem()) {
                throw TransitionRefused::because(self::REASON_TOO_MANY_PHOTOS);
            }
        }

        $stored = $type === MediaType::Video
            ? $this->uploads->storeVideo($file, 'INSPECTION_MEDIA', $inspector)
            : $this->uploads->store($file, 'INSPECTION_MEDIA', $inspector, SafeUploads::PHOTO_TYPES);

        $media = new InspectionMedia(['media_type' => $type, 'gps_lat' => $lat, 'gps_lng' => $lng, 'captured_at' => now()]);
        $media->inspection_id = $inspection->id;
        $media->inspection_item_id = $item?->id;
        $media->stored_file_id = $stored->id;
        $media->save();

        return $media;
    }

    /**
     * D-196: a visit to another premises while the application is already
     * at INSPECTION_REPORT.
     */
    public function recordVisit(Inspection $inspection, User $inspector): void
    {
        DB::transaction(function () use ($inspection, $inspector): void {
            $application = $this->lockedApplication($inspection);

            if ($application->status !== ApplicationStatus::InspectionReport) {
                throw TransitionRefused::because(self::REASON_STAGE);
            }

            $this->ensureEditableBy($inspection, $inspector);

            if ($inspection->conducted_at === null) {
                $inspection->forceFill(['conducted_at' => now()])->save();
                AuditLog::record('inspection_visit_recorded', 'Inspection visit recorded', $inspection, $inspector);
            }
        });
    }

    /**
     * D-196: rescheduling an inspection not yet visited while the
     * application is already at INSPECTION_REPORT (same email as T9).
     */
    public function reschedule(Inspection $inspection, User $registrar, string $plannedDate, string $comment): void
    {
        DB::transaction(function () use ($inspection, $registrar, $plannedDate, $comment): void {
            $application = $this->lockedApplication($inspection);

            if ($application->status !== ApplicationStatus::InspectionReport) {
                throw TransitionRefused::because(self::REASON_STAGE);
            }

            if (! $registrar->hasRole(StaffRole::PesticideRegistrar->value)) {
                throw TransitionRefused::because(self::REASON_REGISTRAR);
            }

            $fresh = Inspection::query()->lockForUpdate()->findOrFail($inspection->id);

            if ($fresh->isSigned() || $fresh->conducted_at !== null) {
                throw TransitionRefused::because(self::REASON_VISITED);
            }

            $fresh->forceFill(['planned_date' => $plannedDate])->save();
            AuditLog::record('inspection_rescheduled', 'Inspection rescheduled', $fresh, $registrar, ['comment' => $comment, 'planned_date' => $plannedDate]);

            $this->notifications->rescheduled($fresh, $registrar);
        });
    }

    private function ensureEditableBy(Inspection $inspection, User $inspector): void
    {
        if ($inspection->inspector_id !== $inspector->id || ! $inspector->hasRole(StaffRole::Inspector->value)) {
            throw TransitionRefused::because(self::REASON_NOT_YOURS);
        }

        if ($inspection->isSigned() || $inspection->status->value !== 'SCHEDULED') {
            throw TransitionRefused::because(self::REASON_LOCKED);
        }
    }

    private function ensureVisited(Inspection $inspection): void
    {
        if ($inspection->conducted_at === null) {
            throw TransitionRefused::because(self::REASON_NOT_VISITED);
        }
    }

    private function application(Inspection $inspection): Application
    {
        return Application::query()->withoutGlobalScope('company')->findOrFail($inspection->application_id);
    }

    private function lockedApplication(Inspection $inspection): Application
    {
        return Application::query()->withoutGlobalScope('company')->whereKey($inspection->application_id)->lockForUpdate()->firstOrFail();
    }
}
