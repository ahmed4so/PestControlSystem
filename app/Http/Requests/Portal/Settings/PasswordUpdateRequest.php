<?php

declare(strict_types=1);

namespace App\Http\Requests\Portal\Settings;

use App\Concerns\PasswordValidationRules;
use App\Domain\Identity\Rules\NotRecentPassword;
use App\Models\CompanyUser;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PasswordUpdateRequest extends FormRequest
{
    use PasswordValidationRules;

    /**
     * New password: policy and not one of the last 5 (05 §3.2, D-082).
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var CompanyUser $account */
        $account = $this->user();

        return [
            'current_password' => $this->currentPasswordRules(),
            'password' => [...$this->passwordRules(), new NotRecentPassword($account)],
        ];
    }
}
