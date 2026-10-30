<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\ReferenceData;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Companies\RegionRequest;
use App\Models\Region;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

/**
 * Regions within an FMS (D-065).
 */
class RegionController extends Controller implements HasMiddleware
{
    /**
     * @return list<Middleware>
     */
    public static function middleware(): array
    {
        return [new Middleware('permission:manage reference data')];
    }

    public function store(RegionRequest $request): RedirectResponse
    {
        Region::query()->create($request->validated());

        return back()->with('success', __('Saved.'));
    }

    public function update(RegionRequest $request, Region $region): RedirectResponse
    {
        $region->update($request->validated());

        return back()->with('success', __('Saved.'));
    }
}
