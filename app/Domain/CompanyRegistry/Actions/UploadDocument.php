<?php

declare(strict_types=1);

namespace App\Domain\CompanyRegistry\Actions;

use App\Domain\CompanyRegistry\Enums\DocumentType;
use App\Domain\CompanyRegistry\Support\ProfileCompleteness;
use App\Domain\Documents\Services\SafeUploads;
use App\Domain\Identity\Enums\StaffRole;
use App\Domain\Notifications\Actions\NotifyRole;
use App\Domain\Notifications\Enums\NotificationCode;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Document;
use App\Models\Person;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Uploads a typed document. A new document of the same type (and person)
 * replaces the current one and keeps the history (D-067). A renewed
 * commerce or tax certificate tells the Registrars (D-073, 07 §3.2).
 */
final class UploadDocument
{
    public const PURPOSE = 'COMPANY_DOCUMENT';

    public function __construct(
        private readonly SafeUploads $uploads,
        private readonly NotifyRole $notifyRole,
    ) {}

    /**
     * @param  array{document_number?: string|null, issued_on?: string|null, expires_on?: string|null}  $details
     */
    public function __invoke(
        Company $company,
        DocumentType $type,
        UploadedFile $file,
        array $details,
        CompanyUser $uploadedBy,
        ?Person $person = null,
    ): Document {
        $stored = $this->uploads->store($file, self::PURPOSE, $uploadedBy);

        return DB::transaction(function () use ($company, $type, $stored, $details, $person): Document {
            $current = Document::query()->withoutGlobalScopes()
                ->where('company_id', $company->id)
                ->where('document_type', $type)
                ->where('person_id', $type->belongsToPerson() ? $person?->id : null)
                ->whereNull('replaced_by_id')
                ->lockForUpdate()
                ->first();

            $document = new Document([
                'document_type' => $type,
                'stored_file_id' => $stored->id,
                'person_id' => $type->belongsToPerson() ? $person?->id : null,
                'document_number' => $type->isCertificate() ? ($details['document_number'] ?? null) : null,
                'issued_on' => $type->isCertificate() ? ($details['issued_on'] ?? null) : null,
                'expires_on' => $type->isCertificate() ? ($details['expires_on'] ?? null) : null,
            ]);
            $document->company()->associate($company);
            $document->save();

            $current?->forceFill(['replaced_by_id' => $document->id])->save();

            ProfileCompleteness::refresh($company);

            if ($current !== null && $type->isCertificate()) {
                ($this->notifyRole)(StaffRole::PesticideRegistrar, NotificationCode::CertificateUpdated, [
                    'company' => $company->name,
                    'certificate' => $type->label(),
                    'url' => route('admin.companies.show', $company),
                ], related: $company);
            }

            return $document;
        });
    }
}
