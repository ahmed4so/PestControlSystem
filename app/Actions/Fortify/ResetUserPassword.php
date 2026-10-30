<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Domain\Identity\Actions\ChangePassword;
use App\Domain\Identity\Rules\NotRecentPassword;
use App\Models\CompanyUser;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

/**
 * Completes a Fortify password reset in either area: policy and reuse
 * check, then ChangePassword, which also unlocks the account (D-081) and
 * ends all its sessions.
 */
class ResetUserPassword implements ResetsUserPasswords
{
    use PasswordValidationRules;

    /**
     * @param  array<string, string>  $input
     */
    public function reset(User|CompanyUser $user, array $input): void
    {
        Validator::make($input, [
            'password' => [...$this->passwordRules(), new NotRecentPassword($user)],
        ])->validate();

        app(ChangePassword::class)($user, $input['password'], event: 'password_reset');
    }
}
