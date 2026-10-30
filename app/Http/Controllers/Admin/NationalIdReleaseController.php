<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Audit\Support\AuditLog;
use App\Domain\CompanyRegistry\Actions\ReleaseNationalId;
use App\Domain\CompanyRegistry\Support\NationalIdHasher;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Companies\ReleaseNationalIdRequest;
use App\Models\Person;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

/**
 * National-ID release by the System Administrator (D-066, D-095, 05 §6):
 * search by the full ID, then release with a reason.
 */
class NationalIdReleaseController extends Controller implements HasMiddleware
{
    /**
     * @return list<Middleware>
     */
    public static function middleware(): array
    {
        return [new Middleware('permission:release national id')];
    }

    public function show(): Response
    {
        return Inertia::render('admin/national-ids/release', ['match' => null]);
    }

    public function find(ReleaseNationalIdRequest $request): Response
    {
        $person = $this->activePerson((string) $request->validated('national_id_no'));

        AuditLog::record('national_id_searched', 'National ID searched for release', $person, $request->user(), [
            'found' => $person !== null,
        ]);

        return Inertia::render('admin/national-ids/release', [
            'searched' => true,
            'nationalId' => $request->validated('national_id_no'),
            'match' => $person === null ? null : [
                'full_name' => $person->full_name,
                'national_id' => $person->maskedNationalId(),
                'company' => $person->company->name,
                'business_registration_no' => $person->company->business_registration_no,
                'is_owner' => $person->company->owner_person_id === $person->id,
            ],
        ]);
    }

    public function release(ReleaseNationalIdRequest $request, ReleaseNationalId $release): RedirectResponse
    {
        $person = $this->activePerson((string) $request->validated('national_id_no'));

        if ($person === null) {
            return redirect()->route('admin.national-ids.show')->with('error', __('No active person has this national ID.'));
        }

        /** @var User $admin */
        $admin = $request->user();

        $release($person, $admin, (string) $request->validated('reason'));

        return redirect()->route('admin.national-ids.show')->with('success', __('The national ID was released. The company was notified.'));
    }

    private function activePerson(string $nationalId): ?Person
    {
        return Person::query()->with('company')->where('active_nid_key', NationalIdHasher::hash($nationalId))->first();
    }
}
