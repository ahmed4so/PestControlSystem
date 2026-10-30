<?php

declare(strict_types=1);

namespace App\Http\Requests\Portal\Company;

use App\Domain\CompanyRegistry\Enums\DocumentType;
use App\Domain\ReferenceData\Support\FieldFormats;
use App\Models\CompanyUser;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * A document upload (05 §4.4). Commerce and tax certificates need a number,
 * an issue date in the past and an expiry date after it (D-017).
 */
class DocumentRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'document_number' => FieldFormats::normaliseRegNo($this->input('document_number')) ?: null,
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var CompanyUser $account */
        $account = $this->user();
        $type = DocumentType::tryFrom((string) $this->input('document_type'));
        $certificate = $type?->isCertificate() ?? false;
        $personDocument = $type?->belongsToPerson() ?? false;

        return [
            'document_type' => ['required', Rule::enum(DocumentType::class)],
            'file' => ['required', 'file', 'max:'.config('documents.max_upload_kb')],
            'document_number' => [$certificate ? 'required' : 'prohibited', ...FieldFormats::regNo()],
            'issued_on' => [$certificate ? 'required' : 'prohibited', 'date', 'before_or_equal:today'],
            'expires_on' => [$certificate ? 'required' : 'prohibited', 'date', 'after:issued_on'],
            'person_id' => [
                $personDocument ? 'required' : 'prohibited',
                'integer',
                Rule::exists('persons', 'id')->where('company_id', $account->company_id ?? 0)->whereNull('released_at')->whereNull('deleted_at'),
            ],
        ];
    }

    public function documentType(): DocumentType
    {
        return DocumentType::from((string) $this->validated('document_type'));
    }
}
