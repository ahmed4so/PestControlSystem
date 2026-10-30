<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\ReferenceData;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Companies\FederalMemberStateRequest;
use App\Models\FederalMemberState;
use App\Models\Region;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

/**
 * FMS and region lists for the System Administrator (D-065, D-184).
 * Values are deactivated, never deleted, so existing addresses keep them.
 */
class FederalMemberStateController extends Controller implements HasMiddleware
{
    /**
     * @return list<Middleware>
     */
    public static function middleware(): array
    {
        return [new Middleware('permission:manage reference data')];
    }

    public function index(): Response
    {
        return Inertia::render('admin/reference-data/fms', [
            'states' => FederalMemberState::query()->with(['regions' => fn ($query) => $query->orderBy('sort_order')->orderBy('name_en')])
                ->orderBy('sort_order')->orderBy('name_en')->get()
                ->map(fn (FederalMemberState $fms) => [
                    ...$fms->only(['id', 'code', 'name_en', 'name_so', 'sort_order', 'is_active']),
                    'regions' => $fms->regions->map(fn (Region $region) => $region->only(['id', 'federal_member_state_id', 'name_en', 'name_so', 'sort_order', 'is_active']))->values(),
                ]),
        ]);
    }

    public function store(FederalMemberStateRequest $request): RedirectResponse
    {
        FederalMemberState::query()->create($request->validated());

        return back()->with('success', __('Saved.'));
    }

    public function update(FederalMemberStateRequest $request, FederalMemberState $fms): RedirectResponse
    {
        $fms->update($request->validated());

        return back()->with('success', __('Saved.'));
    }
}
