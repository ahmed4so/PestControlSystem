<?php

declare(strict_types=1);

namespace App\Domain\Identity\Rules;

use App\Models\CompanyUser;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Hash;

/**
 * The new password must differ from the current one and the previous ones
 * kept in password_histories, the last 5 in all (03 §6, D-082).
 */
final class NotRecentPassword implements ValidationRule
{
    public function __construct(private readonly User|CompanyUser $account) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }

        foreach (self::recentHashes($this->account) as $hash) {
            if (Hash::check($value, $hash)) {
                $fail(__('Choose a password you have not used recently.'));

                return;
            }
        }
    }

    /**
     * The current hash plus the newest previous ones, 5 in all.
     *
     * @return list<string>
     */
    public static function recentHashes(User|CompanyUser $account): array
    {
        $keep = (int) config('identity.password.history', 5);

        $previous = $account->passwordHistories()
            ->latest('id')
            ->limit($keep - 1)
            ->pluck('password_hash')
            ->all();

        return array_values(array_filter([$account->getAuthPassword(), ...$previous]));
    }
}
