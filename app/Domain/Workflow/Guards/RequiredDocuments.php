<?php

declare(strict_types=1);

namespace App\Domain\Workflow\Guards;

use App\Domain\Applications\Enums\Service;
use App\Domain\CompanyRegistry\Enums\DocumentType;
use App\Domain\Workflow\Contracts\Guard;
use App\Domain\Workflow\Engine\GuardResult;
use App\Domain\Workflow\Engine\TransitionContext;
use App\Models\Document;

/**
 * Every company document type the service needs exists (04 §11, D-017,
 * 05 §4.4). The Operator License also needs the equipment inventory
 * (04 §6). Degree and CV belong to the technical person
 * (TechnicalPersonSelected).
 */
final class RequiredDocuments implements Guard
{
    public const REASON = 'Upload your :document.';

    public function check(TransitionContext $context, ?string $param): GuardResult
    {
        $required = [
            DocumentType::CommerceRegistrationCertificate,
            DocumentType::TaxComplianceCertificate,
            DocumentType::MdNationalId,
            DocumentType::CompanyProfile,
        ];

        if ($context->application->service === Service::OperatorLicense) {
            $required[] = DocumentType::EquipmentInventory;
        }

        $present = Document::query()->withoutGlobalScope('company')
            ->where('company_id', $context->application->company_id)
            ->whereNull('replaced_by_id')
            ->pluck('document_type')
            ->map(fn (DocumentType $type) => $type->value)
            ->all();

        $reasons = [];

        foreach ($required as $type) {
            if (! in_array($type->value, $present, true)) {
                $reasons[] = __(self::REASON, ['document' => __($type->label())]);
            }
        }

        return GuardResult::failIfAny($reasons);
    }

    public function needsPayload(): bool
    {
        return false;
    }
}
