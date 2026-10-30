<?php

declare(strict_types=1);

namespace App\Http\Requests\Portal\Company;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * One checklist photo, sent alone so a slow connection re-sends only one
 * file (05 §4.3, D-135). The type is checked from the content by the
 * Documents module.
 */
class ChecklistPhotoRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'photo' => ['required', 'file', 'max:'.config('documents.max_upload_kb')],
            'captured_at' => ['nullable', 'date', 'before_or_equal:now'],
        ];
    }
}
