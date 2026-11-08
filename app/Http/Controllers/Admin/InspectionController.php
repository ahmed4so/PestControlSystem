<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Audit\Support\AuditLog;
use App\Domain\Documents\Exceptions\UnsafeUpload;
use App\Domain\Documents\Services\FileStorage;
use App\Domain\Identity\Enums\StaffRole;
use App\Domain\Inspection\Actions\InspectionActions;
use App\Domain\Inspection\Enums\InspectionItemResult;
use App\Domain\Inspection\Enums\MediaType;
use App\Domain\Inspection\Enums\OverallResult;
use App\Domain\Inspection\Support\InspectionChecklist;
use App\Domain\ReferenceData\Support\FieldFormats;
use App\Domain\Workflow\Engine\WorkflowActor;
use App\Domain\Workflow\Engine\WorkflowEngine;
use App\Domain\Workflow\Enums\WorkflowActionCode;
use App\Domain\Workflow\Exceptions\AlreadyProcessed;
use App\Domain\Workflow\Exceptions\TransitionRefused;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationVersion;
use App\Models\Inspection;
use App\Models\InspectionMedia;
use App\Models\User;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * The inspection of one premises (02 §4.8, procedure 6.5–6.7, D-077,
 * D-136, D-196), built for a phone at the premises. Visiting, rescheduling
 * and signing go through the workflow engine (T9–T11) at the matching
 * stage; while the application is already at INSPECTION_REPORT, a visit or
 * a reschedule of another inspection is an audited Inspection action.
 */
class InspectionController extends Controller
{
    public function show(Request $request, Inspection $inspection): Response
    {
        $application = $this->application($inspection);
        Gate::authorize('view', $application);

        /** @var User $user */
        $user = $request->user();
        $inspection->load(['items', 'media', 'inspector']);
        $version = ApplicationVersion::query()->findOrFail($inspection->application_version_id);
        $premises = collect((array) ($version->snapshot['premises'] ?? []))->firstWhere('id', $inspection->premises_id);
        $answers = $inspection->items->keyBy('item_code');
        $mine = $inspection->inspector_id === $user->id;

        return Inertia::render('admin/inspections/show', [
            'inspection' => [
                'id' => $inspection->id,
                'application_id' => $application->id,
                'reference' => $application->reference,
                'application_status' => $application->status->value,
                'round' => $inspection->round,
                'planned_date' => $inspection->planned_date->format('Y-m-d'),
                'conducted_at' => $inspection->conducted_at?->toIso8601String(),
                'signed_at' => $inspection->signed_at?->toIso8601String(),
                'status' => $inspection->status->value,
                'inspector' => $inspection->inspector?->name,
                'gps_lat' => $inspection->gps_lat,
                'gps_lng' => $inspection->gps_lng,
                'gps_unavailable_reason' => $inspection->gps_unavailable_reason,
                'observed_facts' => $inspection->observed_facts,
                'recommendations' => $inspection->recommendations,
                'deficiencies' => $inspection->deficiencies,
                'overall_result' => $inspection->overall_result?->value,
            ],
            'premises' => $premises,
            'checklist' => collect(InspectionChecklist::items($application->service))->map(fn (string $label, string $code) => [
                'code' => $code,
                'label' => $label,
                'result' => $answers->get($code)?->result->value,
                'note' => $answers->get($code)?->note,
                'photos' => $inspection->media->where('inspection_item_id', $answers->get($code)?->id)->where('media_type', MediaType::Photo)->count(),
            ])->values(),
            'media' => $inspection->media->map(fn (InspectionMedia $media) => [
                'id' => $media->id,
                'type' => $media->media_type->value,
                'item' => $inspection->items->firstWhere('id', $media->inspection_item_id)?->item_code,
                'captured_at' => $media->captured_at?->toIso8601String(),
            ])->values(),
            'can' => [
                'visit' => $mine && $inspection->conducted_at === null && ! $inspection->isSigned()
                    && in_array($application->status, [ApplicationStatus::InspectionScheduled, ApplicationStatus::InspectionReport], true),
                'edit' => $mine && $inspection->conducted_at !== null && ! $inspection->isSigned(),
                'sign' => $mine && $inspection->conducted_at !== null && ! $inspection->isSigned()
                    && $application->status === ApplicationStatus::InspectionReport,
                'reschedule' => $inspection->conducted_at === null && ! $inspection->isSigned()
                    && $user->hasRole(StaffRole::PesticideRegistrar->value),
            ],
            'maxPhotos' => InspectionChecklist::maxPhotosPerItem(),
        ]);
    }

    public function report(Request $request, Inspection $inspection, InspectionActions $actions): RedirectResponse
    {
        Gate::authorize('view', $this->application($inspection));

        $validated = $request->validate([
            'items' => ['nullable', 'array'],
            'items.*.result' => ['nullable', Rule::enum(InspectionItemResult::class)],
            'items.*.note' => ['nullable', 'string', 'max:2000'],
            'observed_facts' => ['nullable', 'string', 'max:5000'],
            'recommendations' => ['nullable', 'string', 'max:5000'],
            'deficiencies' => ['nullable', 'string', 'max:5000'],
            'overall_result' => ['nullable', Rule::enum(OverallResult::class)],
            'gps_lat' => ['nullable', 'numeric', 'between:-2,12.5'],
            'gps_lng' => ['nullable', 'numeric', 'between:40.5,51.5'],
            'gps_unavailable_reason' => ['nullable', 'string', 'max:255'],
        ]);

        return $this->attempt(fn () => $actions->saveReport($inspection, $this->user($request), $validated), __('Saved.'));
    }

    public function media(Request $request, Inspection $inspection, InspectionActions $actions): RedirectResponse
    {
        Gate::authorize('view', $this->application($inspection));

        $validated = $request->validate([
            'file' => ['required', 'file'],
            'type' => ['required', Rule::enum(MediaType::class)],
            'item_code' => ['nullable', 'string', 'max:60'],
            'gps_lat' => ['nullable', 'numeric', 'between:-2,12.5'],
            'gps_lng' => ['nullable', 'numeric', 'between:40.5,51.5'],
        ]);

        return $this->attempt(fn () => $actions->addMedia(
            $inspection,
            $this->user($request),
            $request->file('file'),
            MediaType::from($validated['type']),
            $validated['item_code'] ?? null,
            isset($validated['gps_lat']) ? (float) $validated['gps_lat'] : null,
            isset($validated['gps_lng']) ? (float) $validated['gps_lng'] : null,
        ), __('Saved.'));
    }

    /**
     * The visit: T10 at INSPECTION_SCHEDULED, the D-196 action after.
     */
    public function visit(Request $request, Inspection $inspection, WorkflowEngine $engine, InspectionActions $actions): RedirectResponse
    {
        $application = $this->application($inspection);
        Gate::authorize('view', $application);

        if ($application->status === ApplicationStatus::InspectionScheduled) {
            return $this->attempt(fn () => $engine->perform($application, WorkflowActionCode::RecordInspectionVisit, WorkflowActor::fromRequest($request), $application->status, [
                'inspection_id' => $inspection->id,
            ]), __('Visit recorded.'));
        }

        return $this->attempt(fn () => $actions->recordVisit($inspection, $this->user($request)), __('Visit recorded.'));
    }

    /**
     * A new planned date: T9 at INSPECTION_SCHEDULED, the D-196 action after.
     */
    public function reschedule(Request $request, Inspection $inspection, WorkflowEngine $engine, InspectionActions $actions): RedirectResponse
    {
        $application = $this->application($inspection);
        Gate::authorize('view', $application);

        $validated = $request->validate([
            'planned_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'comment' => ['required', ...FieldFormats::comment()],
        ]);

        if ($application->status === ApplicationStatus::InspectionScheduled) {
            return $this->attempt(fn () => $engine->perform($application, WorkflowActionCode::RescheduleInspection, WorkflowActor::fromRequest($request), $application->status, [
                'inspection_id' => $inspection->id,
                ...$validated,
            ]), __('Inspection moved.'));
        }

        return $this->attempt(fn () => $actions->reschedule($inspection, $this->user($request), $validated['planned_date'], $validated['comment']), __('Inspection moved.'));
    }

    /**
     * Signing the report (T11, password confirmed, D-085).
     */
    public function sign(Request $request, Inspection $inspection, WorkflowEngine $engine): RedirectResponse
    {
        $application = $this->application($inspection);
        Gate::authorize('view', $application);

        $request->validate(['password' => ['required', 'string', 'current_password:web']]);
        $request->session()->put('auth.password_confirmed_at', time());

        return $this->attempt(fn () => $engine->perform($application, WorkflowActionCode::SubmitInspectionReport, WorkflowActor::fromRequest($request), $application->status, [
            'inspection_id' => $inspection->id,
        ]), __('The inspection report is signed.'));
    }

    /**
     * A photo or video of the inspection; every view is audited (D-055).
     */
    public function file(Request $request, Inspection $inspection, InspectionMedia $media, FileStorage $files): HttpResponse
    {
        Gate::authorize('view', $this->application($inspection));
        abort_unless($media->inspection_id === $inspection->id, 404);

        AuditLog::record('inspection_media_viewed', 'Staff opened an inspection photo or video', $media, $request->user());

        return $files->download($media->file);
    }

    /**
     * @param  Closure(): mixed  $work
     */
    private function attempt(Closure $work, string $success): RedirectResponse
    {
        try {
            $work();
        } catch (TransitionRefused $refused) {
            return back()->withErrors(['inspection' => implode(' ', array_map(fn (string $reason) => __($reason), $refused->reasons))]);
        } catch (UnsafeUpload $unsafe) {
            return back()->withErrors(['file' => __($unsafe->getMessage())]);
        } catch (AlreadyProcessed) {
            return back()->with('error', __(AlreadyProcessed::MESSAGE));
        }

        return back()->with('success', $success);
    }

    private function application(Inspection $inspection): Application
    {
        return Application::query()->withoutGlobalScope('company')->findOrFail($inspection->application_id);
    }

    private function user(Request $request): User
    {
        /** @var User $user */
        $user = $request->user();

        return $user;
    }
}
