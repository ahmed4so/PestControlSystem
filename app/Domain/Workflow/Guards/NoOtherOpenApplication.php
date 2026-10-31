<?php

declare(strict_types=1);

namespace App\Domain\Workflow\Guards;

use App\Domain\Workflow\Contracts\Guard;
use App\Domain\Workflow\Engine\GuardResult;
use App\Domain\Workflow\Engine\TransitionContext;
use App\Models\Application;

/**
 * No other open application with the same open key (04 §11, D-044, D-193).
 * Import Permits have no key and are never blocked (D-072). The unique
 * (company_id, open_key) index enforces the same rule in the database.
 */
final class NoOtherOpenApplication implements Guard
{
    public const REASON = 'You already have an open application for this service and category.';

    public function check(TransitionContext $context, ?string $param): GuardResult
    {
        $application = $context->application;
        $key = $application->service->openKey($application->license_category);

        if ($key === null) {
            return GuardResult::pass();
        }

        $other = Application::query()->withoutGlobalScope('company')
            ->where('company_id', $application->company_id)
            ->where('open_key', $key)
            ->whereKeyNot($application->id)
            ->exists();

        return $other ? GuardResult::fail(self::REASON) : GuardResult::pass();
    }

    public function needsPayload(): bool
    {
        return false;
    }
}
