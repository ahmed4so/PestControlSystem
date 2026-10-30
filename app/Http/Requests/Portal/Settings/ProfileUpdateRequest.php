<?php

declare(strict_types=1);

namespace App\Http\Requests\Portal\Settings;

use App\Domain\ReferenceData\Support\FieldFormats;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * The account holder's own details. The login email changes only through
 * the confirmation link (D-093).
 */
class ProfileUpdateRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'full_name' => FieldFormats::normaliseText($this->input('full_name')),
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
            'phone' => ['required', ...FieldFormats::phone()],
            'preferred_locale' => ['required', 'in:en,so'],
        ];
    }
}
