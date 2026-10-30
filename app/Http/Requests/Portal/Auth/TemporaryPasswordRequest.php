<?php

declare(strict_types=1);

namespace App\Http\Requests\Portal\Auth;

use App\Domain\ReferenceData\Support\FieldFormats;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TemporaryPasswordRequest extends FormRequest
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
        return [
            'email' => ['required', ...FieldFormats::email()],
        ];
    }
}
