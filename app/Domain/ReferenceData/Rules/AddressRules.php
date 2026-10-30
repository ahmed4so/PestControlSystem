<?php

declare(strict_types=1);

namespace App\Domain\ReferenceData\Rules;

use App\Domain\ReferenceData\Support\FieldFormats;
use Illuminate\Validation\Rule;

/**
 * Validation for the embedded address (05 §4.5, D-065). With $required
 * false the fields are checked for format only, so a profile can be saved
 * step by step (D-178). The region must belong to the chosen FMS, and only
 * active values can be chosen.
 */
final class AddressRules
{
    /**
     * @param  array<string, mixed>  $input
     * @return array<string, list<mixed>>
     */
    public static function rules(array $input, bool $required = false): array
    {
        $presence = $required ? 'required' : 'nullable';
        $fmsId = $input['address_fms_id'] ?? null;

        return [
            'address_fms_id' => [$presence, 'integer', Rule::exists('federal_member_states', 'id')->where('is_active', true)->whereNull('deleted_at')],
            'address_region_id' => [
                $presence,
                'integer',
                Rule::exists('regions', 'id')
                    ->where('federal_member_state_id', is_numeric($fmsId) ? (int) $fmsId : 0)
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],
            'address_district' => ['nullable', 'string', 'max:100'],
            'address_town_or_market' => [$presence, 'string', 'min:2', 'max:150'],
            'address_street' => ['nullable', 'string', 'max:150'],
            'address_details' => ['nullable', ...FieldFormats::textShort()],
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public static function normalise(array $input): array
    {
        foreach (['address_district', 'address_town_or_market', 'address_street', 'address_details'] as $field) {
            if (array_key_exists($field, $input)) {
                $input[$field] = FieldFormats::normaliseText($input[$field]) ?: null;
            }
        }

        return $input;
    }

    /**
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return [
            'address_region_id.exists' => __('Choose a region of the selected Federal Member State.'),
        ];
    }
}
