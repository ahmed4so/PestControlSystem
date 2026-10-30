<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Settings;

use App\Domain\ReferenceData\Support\FieldFormats;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * A staff member's own profile. The login email is changed only by the
 * System Administrator (D-172).
 */
class ProfileUpdateRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => FieldFormats::normaliseText($this->input('name')),
            'phone' => FieldFormats::normalisePhone($this->input('phone')),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', ...FieldFormats::name()],
            'phone' => ['nullable', ...FieldFormats::phone()],
            'preferred_locale' => ['required', 'in:en,so'],
        ];
    }
}
