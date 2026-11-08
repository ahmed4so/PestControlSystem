<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Applications\Queries\WorkQueues;
use App\Domain\Identity\Enums\StaffRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Staff work queues (04 §15, D-105, 09 Phase 4A row C). Everyone with a
 * workflow permission has "My work"; the other queues need
 * `view applications`, and "By stage" is for the Registrar, Director and
 * DG.
 */
class WorkQueueController extends Controller implements HasMiddleware
{
    public const TABS = ['mine', 'stages', 'waiting', 'flags'];

    /**
     * @return list<Middleware>
     */
    public static function middleware(): array
    {
        return [new Middleware('permission:view applications|view assigned applications')];
    }

    public function __invoke(Request $request, WorkQueues $queues): Response
    {
        /** @var User $user */
        $user = $request->user();
        $tabs = $this->tabsFor($user);
        $tab = in_array($request->query('tab'), $tabs, true) ? (string) $request->query('tab') : 'mine';

        return Inertia::render('admin/queues', [
            'tab' => $tab,
            'tabs' => $tabs,
            'items' => match ($tab) {
                'waiting' => $queues->waitingOnCompany(),
                'flags' => $queues->flags(),
                'stages' => null,
                default => $queues->myWork($user),
            },
            'stages' => $tab === 'stages' ? $queues->byStage() : [],
        ]);
    }

    /**
     * @return list<string>
     */
    private function tabsFor(User $user): array
    {
        $tabs = ['mine'];

        if ($user->can('view applications')) {
            $byStage = collect(WorkQueues::BY_STAGE_ROLES)->contains(fn (StaffRole $role) => $user->hasRole($role->value));

            if ($byStage) {
                $tabs[] = 'stages';
            }

            $tabs[] = 'waiting';
            $tabs[] = 'flags';
        }

        return $tabs;
    }
}
