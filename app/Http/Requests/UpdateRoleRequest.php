<?php

namespace App\Http\Requests;

use App\Domain\Identity\Enums\StaffRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($this->route('role')),
                // The designed staff roles keep their names (D-023, D-051).
                Rule::when(
                    StaffRole::tryFrom((string) $this->route('role')?->name) !== null,
                    [Rule::in([(string) $this->route('role')?->name])],
                ),
            ],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The role name is required.',
            'name.max' => 'The role name must not exceed 255 characters.',
            'name.unique' => 'This role name is already taken.',
            'name.in' => 'This role is part of the approved design and cannot be renamed.',
        ];
    }
}
