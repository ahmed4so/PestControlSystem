<?php

declare(strict_types=1);

namespace App\Domain\Identity\Enums;

/**
 * Staff roles (D-023, D-051, D-106). The value is the spatie role name;
 * code() is the actor code used by the workflow definitions (04 §3).
 */
enum StaffRole: string
{
    case SystemAdministrator = 'System Administrator';
    case PesticideRegistrar = 'Pesticide Registrar';
    case TechnicalReviewer = 'Technical Reviewer';
    case Inspector = 'Inspector';
    case DepartmentalDirector = 'Departmental Director';
    case PaymentOfficer = 'Payment Officer';
    case DirectorGeneral = 'Director General';

    /**
     * Workflow actor code, or null for the System Administrator,
     * which has no workflow role (D-051, D-086).
     */
    public function code(): ?string
    {
        return match ($this) {
            self::SystemAdministrator => null,
            self::PesticideRegistrar => 'REGISTRAR',
            self::TechnicalReviewer => 'TECHNICAL_REVIEWER',
            self::Inspector => 'INSPECTOR',
            self::DepartmentalDirector => 'DEPARTMENTAL_DIRECTOR',
            self::PaymentOfficer => 'PAYMENT_OFFICER',
            self::DirectorGeneral => 'DIRECTOR_GENERAL',
        };
    }

    public function isWorkflowRole(): bool
    {
        return $this->code() !== null;
    }

    public static function fromCode(string $code): self
    {
        foreach (self::cases() as $role) {
            if ($role->code() === $code) {
                return $role;
            }
        }

        throw new \ValueError("Unknown staff role code [{$code}].");
    }

    /**
     * @return list<string>
     */
    public static function workflowRoleNames(): array
    {
        return array_values(array_map(
            fn (self $role): string => $role->value,
            array_filter(self::cases(), fn (self $role): bool => $role->isWorkflowRole()),
        ));
    }
}
