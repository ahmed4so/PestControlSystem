<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Companies;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegionRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'federal_member_state_id' => ['required', 'integer', Rule::exists('federal_member_states', 'id')->whereNull('deleted_at')],
            'name_en' => ['required', 'string', 'min:2', 'max:100'],
            'name_so' => ['required', 'string', 'min:2', 'max:100'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
