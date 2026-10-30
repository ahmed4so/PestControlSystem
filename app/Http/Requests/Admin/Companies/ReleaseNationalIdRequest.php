<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Companies;

use App\Domain\CompanyRegistry\Support\NationalIdHasher;
use App\Domain\ReferenceData\Support\FieldFormats;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * National-ID search and release (05 §6): the full 11-digit ID, and for a
 * release a reason of 10 to 2,000 characters.
 */
class ReleaseNationalIdRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('national_id_no'))) {
            $this->merge(['national_id_no' => NationalIdHasher::normalise($this->input('national_id_no'))]);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'national_id_no' => ['required', ...FieldFormats::nationalId()],
            'reason' => [$this->routeIs('admin.national-ids.release') ? 'required' : 'prohibited', ...FieldFormats::comment()],
        ];
    }
}
