<?php

declare(strict_types=1);

namespace App\Http\Requests\Portal\Settings;

use App\Domain\ReferenceData\Support\FieldFormats;
use App\Models\CompanyUser;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * New login email (05 §3.2, D-093): valid and not used by another company
 * account.
 */
class ChangeLoginEmailRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['email' => FieldFormats::normaliseEmail($this->input('email'))]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var CompanyUser $account */
        $account = $this->user();

        return [
            'email' => [
                'required',
                ...FieldFormats::email(),
                Rule::notIn([$account->email]),
                Rule::unique('company_users', 'email'),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.not_in' => __('This is already your login email.'),
            'email.unique' => __('This email cannot be used. Choose another email address.'),
        ];
    }
}
