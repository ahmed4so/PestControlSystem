<?php

declare(strict_types=1);

namespace App\Domain\Notifications\Actions;

use App\Domain\Identity\Enums\AccountStatus;
use App\Domain\Identity\Enums\StaffRole;
use App\Domain\Notifications\Enums\NotificationCode;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Sends a notification to every active staff user holding a role (D-127).
 * Staff never receive an email for their own action (07 §3.3).
 */
final class NotifyRole
{
    public function __construct(private readonly SendNotification $send) {}

    /**
     * @param  array<string, string>  $data
     */
    public function __invoke(StaffRole $role, NotificationCode $code, array $data = [], ?Model $related = null, ?User $actor = null): int
    {
        $recipients = User::role($role->value)
            ->where('status', AccountStatus::Active)
            ->when($actor !== null, fn ($query) => $query->whereKeyNot($actor?->getKey()))
            ->get();

        foreach ($recipients as $user) {
            ($this->send)($user, $code, $data, related: $related);
        }

        return $recipients->count();
    }
}
