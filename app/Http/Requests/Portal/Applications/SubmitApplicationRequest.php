<?php

declare(strict_types=1);

namespace App\Http\Requests\Portal\Applications;

use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\ReferenceData\Support\FieldFormats;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The declaration of Form 1 (05 §5.1): name (pre-filled), designation,
 * "I confirm the information is true" and password confirmation (D-085).
 * The workflow guards check everything else.
 */
class SubmitApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'expected_status' => ['required', Rule::in([ApplicationStatus::Draft->value, ApplicationStatus::Returned->value])],
            'declaration_name' => ['required', ...FieldFormats::name()],
            'declaration_designation' => ['required', 'string', 'min:2', 'max:150'],
            'confirm' => ['accepted'],
            'password' => ['required', 'string', 'current_password:company'],
            'response_note' => ['nullable', ...FieldFormats::textLong()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'declaration_name' => __('name'),
            'declaration_designation' => __('designation'),
            'confirm' => __('confirmation'),
        ];
    }
}
