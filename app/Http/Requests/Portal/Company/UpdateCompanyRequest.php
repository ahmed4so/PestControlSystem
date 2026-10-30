<?php

declare(strict_types=1);

namespace App\Http\Requests\Portal\Company;

use App\Domain\ReferenceData\Rules\AddressRules;
use App\Domain\ReferenceData\Support\FieldFormats;
use App\Models\CompanyUser;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Company profile (05 §4.1, §4.5). Formats are checked on every save;
 * required fields are counted by the completeness check (D-178), except the
 * name and registration number, which the company always has.
 */
class UpdateCompanyRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(AddressRules::normalise([
            ...$this->all(),
            'name' => FieldFormats::normaliseText($this->input('name')),
            'business_registration_no' => FieldFormats::normaliseRegNo($this->input('business_registration_no')),
            'tax_compliance_no' => FieldFormats::normaliseRegNo($this->input('tax_compliance_no')) ?: null,
            'email' => FieldFormats::normaliseEmail($this->input('email')) ?: null,
            'phone' => FieldFormats::normalisePhone($this->input('phone')) ?: null,
        ]));
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var CompanyUser $account */
        $account = $this->user();

        return [
            'name' => ['required', ...FieldFormats::companyName()],
            'business_registration_no' => ['required', ...FieldFormats::regNo()],
            'tax_compliance_no' => ['nullable', ...FieldFormats::regNo()],
            'email' => ['nullable', ...FieldFormats::email()],
            'phone' => ['nullable', ...FieldFormats::phone()],
            'owner_person_id' => [
                'nullable',
                'integer',
                Rule::exists('persons', 'id')
                    ->where('company_id', $account->company_id ?? 0)
                    ->whereNull('released_at')
                    ->whereNull('deleted_at'),
            ],
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
            'phone.regex' => __('Enter the phone number in international format, for example +252612345678.'),
            'business_registration_no.regex' => __('Use letters, digits, - and / only.'),
            'tax_compliance_no.regex' => __('Use letters, digits, - and / only.'),
        ];
    }
}
