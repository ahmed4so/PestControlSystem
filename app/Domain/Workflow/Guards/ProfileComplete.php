<?php

declare(strict_types=1);

namespace App\Domain\Workflow\Guards;

use App\Domain\CompanyRegistry\Support\ProfileCompleteness;
use App\Domain\Workflow\Contracts\Guard;
use App\Domain\Workflow\Engine\GuardResult;
use App\Domain\Workflow\Engine\TransitionContext;
use App\Models\Company;

/**
 * Company profile, Owner/MD and address complete (04 §11, D-073, D-178).
 */
final class ProfileComplete implements Guard
{
    public const REASON = 'Complete your company profile first.';

    public function check(TransitionContext $context, ?string $param): GuardResult
    {
        $company = Company::query()->withoutGlobalScope('company')->findOrFail($context->application->company_id);
        $missing = ProfileCompleteness::missing($company);

        return $missing === [] ? GuardResult::pass() : GuardResult::fail(self::REASON, ...$missing);
    }

    public function needsPayload(): bool
    {
        return false;
    }
}
