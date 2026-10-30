<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Domain\ReferenceData\Support\FieldFormats;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Staff user details (05 §3.3). Roles change on the role assignment page;
 * passwords are never set here (D-083).
 */
class UpdateUserRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => FieldFormats::normaliseText($this->input('name')),
            'email' => FieldFormats::normaliseEmail($this->input('email')),
            'phone' => FieldFormats::normalisePhone($this->input('phone')),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var User $user */
        $user = $this->route('user');

        return [
            'name' => ['required', ...FieldFormats::name()],
            'email' => ['required', ...FieldFormats::email(), Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', ...FieldFormats::phone()],
            'preferred_locale' => ['required', 'in:en,so'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.unique' => __('This email is already used by a staff account.'),
            'name.regex' => __('Use letters, spaces, apostrophes, hyphens and full stops only.'),
            'phone.regex' => __('Enter the phone number in international format, for example +252612345678.'),
        ];
    }
}
