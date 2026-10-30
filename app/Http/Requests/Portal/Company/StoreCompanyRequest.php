<?php

declare(strict_types=1);

namespace App\Http\Requests\Portal\Company;

use App\Domain\ReferenceData\Support\FieldFormats;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Profile set-up: the company name and the business registration number,
 * the dedup key (02 §6.2, 05 §4.1).
 */
class StoreCompanyRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => FieldFormats::normaliseText($this->input('name')),
            'business_registration_no' => FieldFormats::normaliseRegNo($this->input('business_registration_no')),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', ...FieldFormats::companyName()],
            'business_registration_no' => ['required', ...FieldFormats::regNo()],
        ];
    }
}
