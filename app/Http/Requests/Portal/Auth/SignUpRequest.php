<?php

declare(strict_types=1);

namespace App\Http\Requests\Portal\Auth;

use App\Domain\ReferenceData\Support\FieldFormats;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Company sign-up (05 §3.1, D-063). The email is not checked for
 * uniqueness here, so the form never reveals whether an account exists.
 */
class SignUpRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'full_name' => FieldFormats::normaliseText($this->input('full_name')),
            'email' => FieldFormats::normaliseEmail($this->input('email')),
            'phone' => FieldFormats::normalisePhone($this->input('phone')),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', ...FieldFormats::name()],
            'email' => ['required', ...FieldFormats::email()],
            'phone' => ['required', ...FieldFormats::phone()],
            'consent' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'full_name.regex' => __('Use letters, spaces, apostrophes, hyphens and full stops only.'),
            'phone.regex' => __('Enter the phone number in international format, for example +252612345678.'),
            'consent.accepted' => __('You must accept the privacy notice to create an account.'),
        ];
    }
}
