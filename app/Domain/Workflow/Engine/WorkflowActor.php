<?php

declare(strict_types=1);

namespace App\Domain\Workflow\Engine;

use App\Models\CompanyUser;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Who performs a workflow action, and in what circumstances: when they last
 * confirmed their password (D-085) and where they act from (WorkflowAction
 * and the declaration record the IP and user agent).
 */
final readonly class WorkflowActor
{
    public function __construct(
        public User|CompanyUser $user,
        public ?int $passwordConfirmedAt = null,
        public ?string $ip = null,
        public ?string $userAgent = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $user = $request->user();

        if (! $user instanceof User && ! $user instanceof CompanyUser) {
            throw new \LogicException('A workflow action needs a logged-in account.');
        }

        $confirmedAt = $request->hasSession() ? $request->session()->get('auth.password_confirmed_at') : null;

        return new self(
            $user,
            is_numeric($confirmedAt) ? (int) $confirmedAt : null,
            $request->ip(),
            substr((string) $request->userAgent(), 0, 255),
        );
    }

    public function isCompany(): bool
    {
        return $this->user instanceof CompanyUser;
    }

    /**
     * Confirmed within the account's window: 15 minutes for staff,
     * 30 minutes for companies (D-085).
     */
    public function passwordRecentlyConfirmed(): bool
    {
        return $this->passwordConfirmedAt !== null
            && now()->getTimestamp() - $this->passwordConfirmedAt <= $this->user->area()->passwordConfirmSeconds();
    }
}
