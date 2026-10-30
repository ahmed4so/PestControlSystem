<?php

declare(strict_types=1);

namespace App\Http\Requests\Portal\Company;

use App\Domain\CompanyRegistry\Support\NationalIdHasher;
use App\Domain\ReferenceData\Rules\AddressRules;
use App\Domain\ReferenceData\Support\Countries;
use App\Domain\ReferenceData\Support\FieldFormats;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * A person (05 §4.2). The name and the national ID (11 digits, D-133) are
 * needed on every save, because the ID is the dedup key (D-066, D-132).
 * The other fields are checked for format; completeness counts the rest
 * (D-178).
 */
class PersonRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(AddressRules::normalise([
            ...$this->all(),
            'full_name' => FieldFormats::normaliseText($this->input('full_name')),
            'national_id_no' => is_string($this->input('national_id_no')) ? NationalIdHasher::normalise($this->input('national_id_no')) : $this->input('national_id_no'),
            'nationality' => is_string($this->input('nationality')) ? strtoupper($this->input('nationality')) : null,
            'email' => FieldFormats::normaliseEmail($this->input('email')) ?: null,
            'phone' => FieldFormats::normalisePhone($this->input('phone')) ?: null,
            'qualification' => FieldFormats::normaliseText($this->input('qualification')) ?: null,
            'institution' => FieldFormats::normaliseText($this->input('institution')) ?: null,
        ]));
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', ...FieldFormats::name()],
            'national_id_no' => ['required', ...FieldFormats::nationalId()],
            'nationality' => ['nullable', Rule::in(Countries::codes())],
            'email' => ['nullable', ...FieldFormats::email()],
            'phone' => ['nullable', ...FieldFormats::phone()],
            'qualification' => ['nullable', ...FieldFormats::textShort()],
            'institution' => ['nullable', ...FieldFormats::textShort()],
            'years_of_experience' => ['nullable', 'integer', 'min:0', 'max:60'],
            'training_pcp_handling' => ['nullable', ...FieldFormats::textLong()],
            'training_fumigation' => ['nullable', ...FieldFormats::textLong()],
            ...AddressRules::rules($this->all()),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            ...AddressRules::messages(),
            'national_id_no.regex' => __('The national ID must be exactly 11 digits.'),
            'full_name.regex' => __('Use letters, spaces, apostrophes, hyphens and full stops only.'),
            'phone.regex' => __('Enter the phone number in international format, for example +252612345678.'),
        ];
    }
}
