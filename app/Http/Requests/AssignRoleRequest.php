<?php

namespace App\Http\Requests;

use App\Domain\Identity\Enums\StaffRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Spatie\Permission\Models\Role;

class AssignRoleRequest extends FormRequest
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
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
        ];
    }

    /**
     * The System Administrator cannot also hold a workflow role (D-086).
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $names = Role::query()
                    ->whereIn('id', (array) $this->input('role_ids', []))
                    ->pluck('name');

                if ($names->contains(StaffRole::SystemAdministrator->value)
                    && $names->intersect(StaffRole::workflowRoleNames())->isNotEmpty()) {
                    $validator->errors()->add(
                        'role_ids',
                        'The System Administrator role cannot be combined with a workflow role.',
                    );
                }
            },
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'Please select a user.',
            'user_id.exists' => 'The selected user does not exist.',
            'role_ids.array' => 'Roles must be provided as an array.',
            'role_ids.*.integer' => 'Each role ID must be a valid integer.',
            'role_ids.*.exists' => 'One or more selected roles do not exist.',
        ];
    }
}
