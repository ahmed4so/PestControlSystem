<?php

declare(strict_types=1);

namespace App\Domain\Workflow\Enums;

use App\Domain\Identity\Enums\StaffRole;

/**
 * Workflow actors (04 §3). Staff codes map to the spatie role names through
 * StaffRole; the System Administrator has no actor code (D-051, D-086).
 */
enum ActorRole: string
{
    case Company = 'COMPANY';
    case Registrar = 'REGISTRAR';
    case TechnicalReviewer = 'TECHNICAL_REVIEWER';
    case Inspector = 'INSPECTOR';
    case DepartmentalDirector = 'DEPARTMENTAL_DIRECTOR';
    case PaymentOfficer = 'PAYMENT_OFFICER';
    case DirectorGeneral = 'DIRECTOR_GENERAL';
    case System = 'SYSTEM';

    public function isStaff(): bool
    {
        return $this !== self::Company && $this !== self::System;
    }

    /**
     * The staff role that may act, or null for COMPANY and SYSTEM.
     */
    public function staffRole(): ?StaffRole
    {
        return $this->isStaff() ? StaffRole::fromCode($this->value) : null;
    }
}
