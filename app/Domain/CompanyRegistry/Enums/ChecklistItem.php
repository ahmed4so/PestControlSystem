<?php

declare(strict_types=1);

namespace App\Domain\CompanyRegistry\Enums;

/**
 * Facility and safety checklist items (02 §4.4, D-018). Forms 1 and 2 use
 * the first six; Form 2 (Operator License) adds the last three.
 */
enum ChecklistItem: string
{
    case Ventilation = 'VENTILATION';
    case FireExtinguishers = 'FIRE_EXTINGUISHERS';
    case Ppe = 'PPE';
    case SpillManagement = 'SPILL_MANAGEMENT';
    case WasteDisposal = 'WASTE_DISPOSAL';
    case FirstAidEmergencyPlan = 'FIRST_AID_EMERGENCY_PLAN';
    case FumigationSheets = 'FUMIGATION_SHEETS';
    case SandSnakes = 'SAND_SNAKES';
    case EquipmentInventory = 'EQUIPMENT_INVENTORY';

    public const MAX_PHOTOS = 5;

    public function label(): string
    {
        return match ($this) {
            self::Ventilation => 'Ventilation',
            self::FireExtinguishers => 'Fire extinguishers',
            self::Ppe => 'Personal protective equipment',
            self::SpillManagement => 'Spill management',
            self::WasteDisposal => 'Waste disposal',
            self::FirstAidEmergencyPlan => 'First aid and emergency plan',
            self::FumigationSheets => 'Fumigation sheets',
            self::SandSnakes => 'Sand snakes',
            self::EquipmentInventory => 'Equipment inventory',
        };
    }

    /**
     * Items required by Form 1 (Company License).
     *
     * @return list<self>
     */
    public static function formOneItems(): array
    {
        return [
            self::Ventilation,
            self::FireExtinguishers,
            self::Ppe,
            self::SpillManagement,
            self::WasteDisposal,
            self::FirstAidEmergencyPlan,
        ];
    }

    /**
     * Items required by Form 2 (Operator License).
     *
     * @return list<self>
     */
    public static function formTwoItems(): array
    {
        return self::cases();
    }
}
