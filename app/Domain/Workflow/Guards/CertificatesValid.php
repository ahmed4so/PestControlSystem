<?php

declare(strict_types=1);

namespace App\Domain\Workflow\Guards;

use App\Domain\CompanyRegistry\Enums\DocumentType;
use App\Domain\Workflow\Contracts\Guard;
use App\Domain\Workflow\Engine\GuardResult;
use App\Domain\Workflow\Engine\TransitionContext;
use App\Models\Document;

/**
 * The current commerce and tax certificates exist and have not expired
 * (04 §11, D-017).
 */
final class CertificatesValid implements Guard
{
    public const REASON_MISSING = 'Upload your current :certificate.';

    public const REASON_EXPIRED = 'Your :certificate has expired. Upload the renewed certificate.';

    public function check(TransitionContext $context, ?string $param): GuardResult
    {
        $today = now()->setTimezone((string) config('app.display_timezone'))->toDateString();
        $reasons = [];

        foreach ([DocumentType::CommerceRegistrationCertificate, DocumentType::TaxComplianceCertificate] as $type) {
            $document = Document::query()->withoutGlobalScope('company')
                ->where('company_id', $context->application->company_id)
                ->where('document_type', $type)
                ->whereNull('replaced_by_id')
                ->first();

            if ($document === null || $document->expires_on === null) {
                $reasons[] = __(self::REASON_MISSING, ['certificate' => __($type->label())]);
            } elseif ($document->expires_on->toDateString() < $today) {
                $reasons[] = __(self::REASON_EXPIRED, ['certificate' => __($type->label())]);
            }
        }

        return GuardResult::failIfAny($reasons);
    }

    public function needsPayload(): bool
    {
        return false;
    }
}
