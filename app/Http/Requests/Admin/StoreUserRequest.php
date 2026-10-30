<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Domain\Identity\Enums\StaffRole;
use App\Domain\ReferenceData\Support\FieldFormats;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Spatie\Permission\Models\Role;

/**
 * New staff user (05 §3.3). No password field: the user gets an emailed
 * temporary password (D-083).
 */
class StoreUserRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => FieldFormats::normaliseText($this->input('name')),
            'email' => FieldFormats::normaliseEmail($this->input('email')),
            'phone' => FieldFormats::normalisePhone($this->input('phone')),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', ...FieldFormats::name()],
            'email' => ['required', ...FieldFormats::email(), Rule::unique('users', 'email')],
            'phone' => ['nullable', ...FieldFormats::phone()],
            'preferred_locale' => ['required', 'in:en,so'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', Rule::exists('roles', 'name')->where('guard_name', 'web')],
        ];
    }

    /**
     * @return list<callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $roles = collect((array) $this->input('roles', []));

                if ($roles->contains(StaffRole::SystemAdministrator->value)
                    && $roles->intersect(StaffRole::workflowRoleNames())->isNotEmpty()) {
                    $validator->errors()->add('roles', __('The System Administrator role cannot be combined with a workflow role.'));
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.unique' => __('This email is already used by a staff account.'),
            'name.regex' => __('Use letters, spaces, apostrophes, hyphens and full stops only.'),
            'phone.regex' => __('Enter the phone number in international format, for example +252612345678.'),
        ];
    }

    /**
     * @return list<string>
     */
    public function roleNames(): array
    {
        return array_values(Role::query()->where('guard_name', 'web')
            ->whereIn('name', (array) $this->validated('roles'))
            ->pluck('name')
            ->all());
    }
}
