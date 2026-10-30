<?php

declare(strict_types=1);

namespace App\Domain\Identity\Support;

use App\Models\CompanyUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Ends an account's sessions in its own area's session table (03 §6, §9).
 * Staff and company sessions are kept in separate tables, so ending one
 * realm's sessions never touches the other (D-048).
 */
final class AccountSessions
{
    public static function end(User|CompanyUser $account, ?string $exceptSessionId = null): void
    {
        DB::table($account->area()->sessionTable())
            ->where('user_id', $account->getKey())
            ->when($exceptSessionId !== null, fn ($query) => $query->where('id', '!=', $exceptSessionId))
            ->delete();
    }
}
