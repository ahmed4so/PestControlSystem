<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Notifications\Enums\NotificationStatus;
use App\Http\Controllers\Controller;
use App\Models\NotificationLog;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Emails that failed after all retries, for the System Administrator
 * (07 §1, D-129).
 */
class FailedEmailController extends Controller implements HasMiddleware
{
    /**
     * @return list<Middleware>
     */
    public static function middleware(): array
    {
        return [new Middleware('permission:manage system settings')];
    }

    public function index(): Response
    {
        $logs = NotificationLog::query()
            ->where('status', NotificationStatus::Failed)
            ->latest()
            ->paginate(50)
            ->through(fn (NotificationLog $log): array => [
                'id' => $log->id,
                'email' => $log->email,
                'template' => $log->template->value,
                'error' => $log->error,
                'created_at' => $log->created_at?->toIso8601String(),
            ]);

        return Inertia::render('admin/failed-emails/index', ['logs' => $logs]);
    }
}
