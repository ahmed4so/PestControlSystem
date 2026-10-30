<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Companies;

use App\Models\FederalMemberState;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FederalMemberStateRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('code'))) {
            $this->merge(['code' => strtoupper(trim($this->input('code')))]);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $current = $this->route('fms');

        return [
            'code' => ['required', 'string', 'max:20', 'regex:/^[A-Z0-9_]+$/', Rule::unique('federal_member_states', 'code')->ignore($current instanceof FederalMemberState ? $current->id : null)],
            'name_en' => ['required', 'string', 'min:2', 'max:100'],
            'name_so' => ['required', 'string', 'min:2', 'max:100'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
