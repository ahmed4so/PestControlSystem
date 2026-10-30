<?php

declare(strict_types=1);

namespace App\Http\Requests\Portal\Company;

use App\Domain\ReferenceData\Support\FieldFormats;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * One checklist answer (05 §4.3).
 */
class ChecklistItemRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'is_available' => ['required', 'boolean'],
            'notes' => ['nullable', ...FieldFormats::textLong()],
        ];
    }
}
