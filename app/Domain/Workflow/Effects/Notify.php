<?php

declare(strict_types=1);

namespace App\Domain\Workflow\Effects;

use App\Domain\Notifications\Actions\NotifyWorkflowEvent;
use App\Domain\Notifications\Enums\NotificationCode;
use App\Domain\Workflow\Contracts\Effect;
use App\Domain\Workflow\Engine\TransitionContext;
use LogicException;

/**
 * `Notify:<CODE>` with a 07 §3.3 code (D-198). Recipients are chosen once
 * the new status is saved; the email is queued after the commit, so a
 * refused or failed transition never sends anything (D-129). A code whose
 * recipients come in a later phase throws, which rolls the transition back
 * (D-188).
 */
final class Notify implements Effect
{
    public function __construct(private readonly NotifyWorkflowEvent $notifier) {}

    public function apply(TransitionContext $context, ?string $param): void
    {
        $code = NotificationCode::tryFrom((string) $param)
            ?? throw new LogicException("Unknown email code [{$param}].");

        if (! $this->notifier->supports($code)) {
            throw new LogicException("The email [{$code->value}] is built in a later phase (D-188).");
        }

        $context->afterStatusChange(fn (TransitionContext $context, string $from) => $this->notifier->send($code, $context, $from));
    }

    public function payloadRules(?string $param): array
    {
        return [];
    }
}
