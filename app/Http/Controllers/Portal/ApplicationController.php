<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Domain\Applications\Actions\CreateDraft;
use App\Domain\Applications\Actions\SelectApplicationData;
use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Applications\Enums\LicenseCategory;
use App\Domain\Applications\Support\CompanyApplicationView;
use App\Domain\Workflow\Engine\WorkflowActor;
use App\Domain\Workflow\Engine\WorkflowEngine;
use App\Domain\Workflow\Enums\WorkflowActionCode;
use App\Domain\Workflow\Exceptions\AlreadyProcessed;
use App\Domain\Workflow\Exceptions\TransitionRefused;
use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\Applications\SubmitApplicationRequest;
use App\Models\Application;
use App\Models\ApplicationVersion;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Person;
use App\Models\Premises;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The company's applications (Form 1, 09 Phase 4A row A). Another
 * company's application gives 404 (BelongsToCompany). Submit and resubmit
 * go through the workflow engine only (D-096, T1, T25).
 */
class ApplicationController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Application::class);

        return Inertia::render('portal/applications/index', [
            'applications' => Application::query()->latest('id')->get()->map(fn (Application $application) => [
                'id' => $application->id,
                'reference' => $application->reference,
                'service' => $application->service->label(),
                'category' => $application->license_category?->label(),
                'status' => $application->status->companyLabel(),
                'updated_at' => $application->updated_at?->toIso8601String(),
            ]),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Application::class);

        return Inertia::render('portal/applications/create', [
            'categories' => array_map(fn (LicenseCategory $category) => [
                'value' => $category->value,
                'label' => $category->label(),
            ], LicenseCategory::cases()),
        ]);
    }

    public function store(Request $request, CreateDraft $createDraft): RedirectResponse
    {
        Gate::authorize('create', Application::class);

        $validated = $request->validate([
            'license_category' => ['required', Rule::enum(LicenseCategory::class)],
        ]);

        $application = $createDraft($this->company($request), LicenseCategory::from($validated['license_category']));

        return redirect()->route('portal.applications.show', $application)->with('success', __('Draft created.'));
    }

    public function show(Request $request, Application $application, WorkflowEngine $engine): Response
    {
        Gate::authorize('view', $application);

        /** @var CompanyUser $account */
        $account = $request->user();
        $editable = Gate::allows('update', $application);
        $submit = collect($engine->availableActions($application, $account))
            ->first(fn (array $action) => in_array($action['action'], [WorkflowActionCode::Submit->value, WorkflowActionCode::Resubmit->value], true));

        return Inertia::render('portal/applications/show', [
            'application' => [
                'id' => $application->id,
                'reference' => $application->reference,
                'service' => $application->service->label(),
                'category' => $application->license_category?->label(),
                'type' => $application->application_type->value,
                'status' => $application->status->value,
                'status_label' => $application->status->companyLabel(),
                'current_version_no' => $application->current_version_no,
            ],
            'editable' => $editable,
            'premises' => $editable ? Premises::query()->orderBy('label')->get(['id', 'label', 'address_town_or_market'])->map(fn (Premises $premises) => [
                'id' => $premises->id,
                'label' => $premises->label,
                'town' => $premises->address_town_or_market,
            ]) : [],
            'persons' => $editable ? Person::query()->whereNull('released_at')->orderBy('full_name')->get(['id', 'full_name', 'qualification'])->map(fn (Person $person) => [
                'id' => $person->id,
                'name' => $person->full_name,
                'qualification' => $person->qualification,
            ]) : [],
            'selected' => [
                'premises' => $application->premises()->pluck('premises.id'),
                'person_id' => $application->technicalPersons()->value('persons.id'),
            ],
            'submit' => $submit === null ? null : [
                'action' => $submit['action'],
                'blocked_by' => array_map(fn (string $reason) => __($reason), $submit['blocked_by']),
            ],
            'declarationName' => $account->full_name,
            'returned' => CompanyApplicationView::returned($application),
            'inspections' => CompanyApplicationView::inspections($application),
            'versions' => $application->versions()->get(['id', 'version_no', 'submitted_at'])->map(fn (ApplicationVersion $version) => [
                'version_no' => $version->version_no,
                'submitted_at' => $version->submitted_at->toIso8601String(),
            ]),
            'history' => CompanyApplicationView::history($application),
        ]);
    }

    public function premises(Request $request, Application $application, SelectApplicationData $select): RedirectResponse
    {
        Gate::authorize('update', $application);

        $validated = $request->validate([
            'premises' => ['present', 'array'],
            'premises.*' => ['integer'],
        ]);

        $select->premises($application, array_map('intval', $validated['premises']));

        return back()->with('success', __('Saved.'));
    }

    public function technicalPerson(Request $request, Application $application, SelectApplicationData $select): RedirectResponse
    {
        Gate::authorize('update', $application);

        $validated = $request->validate(['person_id' => ['nullable', 'integer']]);

        $select->technicalPerson($application, isset($validated['person_id']) ? (int) $validated['person_id'] : null);

        return back()->with('success', __('Saved.'));
    }

    public function submit(SubmitApplicationRequest $request, Application $application, WorkflowEngine $engine): RedirectResponse
    {
        Gate::authorize('update', $application);

        // The password was just checked by the request (D-085).
        $request->session()->put('auth.password_confirmed_at', time());

        $expected = ApplicationStatus::from($request->string('expected_status')->toString());
        $action = $expected === ApplicationStatus::Returned ? WorkflowActionCode::Resubmit : WorkflowActionCode::Submit;

        try {
            $engine->perform($application, $action, WorkflowActor::fromRequest($request), $expected, $request->safe()->only([
                'declaration_name', 'declaration_designation', 'response_note',
            ]));
        } catch (TransitionRefused $refused) {
            return back()->withErrors(['submit' => implode(' ', array_map(fn (string $reason) => __($reason), $refused->reasons))]);
        } catch (AlreadyProcessed) {
            return back()->with('error', __(AlreadyProcessed::MESSAGE));
        }

        return redirect()->route('portal.applications.show', $application)->with('success', __('Your application was submitted.'));
    }

    private function company(Request $request): Company
    {
        /** @var CompanyUser $account */
        $account = $request->user();

        return Company::query()->findOrFail($account->company_id);
    }
}
