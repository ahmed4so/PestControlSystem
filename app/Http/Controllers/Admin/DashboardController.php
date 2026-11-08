<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Applications\Queries\WorkQueues;
use App\Domain\Identity\Enums\StaffRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Staff dashboard: the member's roles, the size of their "My work" queue
 * when they hold a workflow permission, and shortcuts the page builds from
 * the shared permissions.
 */
class DashboardController extends Controller
{
    public function __invoke(Request $request, WorkQueues $queues): Response
    {
        /** @var User $user */
        $user = $request->user();
        $works = $user->can('view applications') || $user->can('view assigned applications');

        return Inertia::render('admin/dashboard', [
            'roles' => $user->getRoleNames()->values()->all(),
            'myWork' => $works ? $queues->myWork($user)->total() : null,
            'workflowRoles' => collect(StaffRole::cases())
                ->reject(fn (StaffRole $role) => $role === StaffRole::SystemAdministrator)
                ->map(fn (StaffRole $role) => $role->value)
                ->values()
                ->all(),
        ]);
    }
}
