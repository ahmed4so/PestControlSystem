<?php

declare(strict_types=1);

namespace App\Domain\Workflow\Guards;

use App\Domain\CompanyRegistry\Enums\DocumentType;
use App\Domain\Workflow\Contracts\Guard;
use App\Domain\Workflow\Engine\GuardResult;
use App\Domain\Workflow\Engine\TransitionContext;
use App\Models\Document;
use App\Models\Person;

/**
 * Exactly one technical person, of the company and not released, with a
 * qualification, a degree certificate and a CV (04 §11, Forms 1 and 2,
 * 05 §4.2).
 */
final class TechnicalPersonSelected implements Guard
{
    public const REASON_ONE = 'Select one technical person.';

    public const REASON_QUALIFICATION = 'Add the technical person\'s qualification.';

    public const REASON_DOCUMENT = 'Upload the technical person\'s :document.';

    public function check(TransitionContext $context, ?string $param): GuardResult
    {
        $persons = $context->application->technicalPersons()->withoutGlobalScope('company')->get();

        if ($persons->count() !== 1) {
            return GuardResult::fail(self::REASON_ONE);
        }

        /** @var Person $person */
        $person = $persons->first();

        if ($person->company_id !== $context->application->company_id || $person->isReleased()) {
            return GuardResult::fail(self::REASON_ONE);
        }

        $reasons = blank($person->qualification) ? [self::REASON_QUALIFICATION] : [];

        foreach ([DocumentType::DegreeCertificate, DocumentType::Cv] as $type) {
            $exists = Document::query()->withoutGlobalScope('company')
                ->where('person_id', $person->id)
                ->where('document_type', $type)
                ->whereNull('replaced_by_id')
                ->exists();

            if (! $exists) {
                $reasons[] = __(self::REASON_DOCUMENT, ['document' => __($type->label())]);
            }
        }

        return GuardResult::failIfAny($reasons);
    }

    public function needsPayload(): bool
    {
        return false;
    }
}
