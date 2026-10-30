<?php

declare(strict_types=1);

namespace App\Http\Requests\Portal\Company;

use App\Domain\CompanyRegistry\Enums\PremisesType;
use App\Domain\ReferenceData\Rules\AddressRules;
use App\Domain\ReferenceData\Support\FieldFormats;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Premises (05 §4.3, §4.5). GPS is optional but must be inside Somalia.
 */
class PremisesRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(AddressRules::normalise([
            ...$this->all(),
            'label' => FieldFormats::normaliseText($this->input('label')),
            'waste_disposal_method' => FieldFormats::normaliseText($this->input('waste_disposal_method')) ?: null,
            'gps_lat' => $this->filled('gps_lat') ? $this->input('gps_lat') : null,
            'gps_lng' => $this->filled('gps_lng') ? $this->input('gps_lng') : null,
        ]));
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'min:2', 'max:100'],
            'premises_type' => ['required', Rule::enum(PremisesType::class)],
            'gps_lat' => ['nullable', 'numeric', 'between:-2,12.5', 'required_with:gps_lng'],
            'gps_lng' => ['nullable', 'numeric', 'between:40.5,51.5', 'required_with:gps_lat'],
            'waste_disposal_method' => ['nullable', ...FieldFormats::textShort()],
            ...AddressRules::rules($this->all(), required: true),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            ...AddressRules::messages(),
            'gps_lat.between' => __('The location must be inside Somalia.'),
            'gps_lng.between' => __('The location must be inside Somalia.'),
        ];
    }
}
