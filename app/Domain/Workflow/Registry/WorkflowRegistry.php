<?php

declare(strict_types=1);

namespace App\Domain\Workflow\Registry;

use App\Domain\Applications\Enums\ApplicationType;
use App\Domain\Workflow\Contracts\Effect;
use App\Domain\Workflow\Contracts\Guard;
use App\Domain\Workflow\Effects\ApplicationFieldEffect;
use App\Domain\Workflow\Effects\CreateAssignment;
use App\Domain\Workflow\Effects\CreateVersionSnapshot;
use App\Domain\Workflow\Effects\EndAssignment;
use App\Domain\Workflow\Effects\GenerateReference;
use App\Domain\Workflow\Effects\Notify;
use App\Domain\Workflow\Effects\NotYetAvailableEffect;
use App\Domain\Workflow\Guards\ActiveDelegation;
use App\Domain\Workflow\Guards\AssigneeHasRole;
use App\Domain\Workflow\Guards\CertificatesValid;
use App\Domain\Workflow\Guards\NoOpenAssignment;
use App\Domain\Workflow\Guards\NoOtherOpenApplication;
use App\Domain\Workflow\Guards\NotYetAvailableGuard;
use App\Domain\Workflow\Guards\PremisesSelected;
use App\Domain\Workflow\Guards\ProfileComplete;
use App\Domain\Workflow\Guards\RequiredDocuments;
use App\Domain\Workflow\Guards\ReviewItemsGuard;
use App\Domain\Workflow\Guards\Segregation;
use App\Domain\Workflow\Guards\TechnicalPersonSelected;
use Closure;
use InvalidArgumentException;

/**
 * The guard and effect catalogues (04 §11–§12). Definitions name them as
 * `Name` or `Name:param`. Names whose data arrives in a later phase are
 * registered as not-yet-available placeholders that fail closed (D-188).
 */
final class WorkflowRegistry
{
    /** @var array<string, Closure(): Guard> */
    private array $guards = [];

    /** @var array<string, Closure(): Effect> */
    private array $effects = [];

    public function __construct()
    {
        $renewalOrAmendment = [ApplicationType::Renewal, ApplicationType::Amendment];

        $this->guards = [
            'ProfileComplete' => fn () => app(ProfileComplete::class),
            'CertificatesValid' => fn () => app(CertificatesValid::class),
            'RequiredDocuments' => fn () => app(RequiredDocuments::class),
            'TechnicalPersonSelected' => fn () => app(TechnicalPersonSelected::class),
            'PremisesSelected' => fn () => app(PremisesSelected::class),
            'NoOtherOpenApplication' => fn () => app(NoOtherOpenApplication::class),
            'ReviewItemsAllCompliant' => fn () => ReviewItemsGuard::allCompliant(),
            'ReviewItemsHaveDeficiency' => fn () => ReviewItemsGuard::haveDeficiency(),
            'ReviewItemsRecorded' => fn () => ReviewItemsGuard::recorded(),
            'NoOpenAssignment' => fn () => app(NoOpenAssignment::class),
            'Segregation' => fn () => app(Segregation::class),
            'ActiveDelegation' => fn () => app(ActiveDelegation::class),
            'AssigneeHasRole' => fn () => app(AssigneeHasRole::class),
            // Later phases (D-188).
            'InspectionsSigned' => fn () => new NotYetAvailableGuard('InspectionsSigned', 'Phase 4A'),
            'InspectionReportComplete' => fn () => new NotYetAvailableGuard('InspectionReportComplete', 'Phase 4A'),
            'InspectionPlanProvided' => fn () => new NotYetAvailableGuard('InspectionPlanProvided', 'Phase 4A'),
            'FeeScheduleExists' => fn () => new NotYetAvailableGuard('FeeScheduleExists', 'Phase 4B'),
            'AmountMatchesLetter' => fn () => new NotYetAvailableGuard('AmountMatchesLetter', 'Phase 4B'),
            'ReceiptNotUsed' => fn () => new NotYetAvailableGuard('ReceiptNotUsed', 'Phase 4B'),
            'PaymentCleared' => fn () => new NotYetAvailableGuard('PaymentCleared', 'Phase 4B'),
            'SignerHasSignature' => fn () => new NotYetAvailableGuard('SignerHasSignature', 'Phase 4B'),
            'PreviousLicenseActive' => fn () => new NotYetAvailableGuard('PreviousLicenseActive', 'Phase 4B', $renewalOrAmendment),
            'RenewalWindowOpen' => fn () => new NotYetAvailableGuard('RenewalWindowOpen', 'Phase 4B', [ApplicationType::Renewal]),
            'ImportationLicenseActive' => fn () => new NotYetAvailableGuard('ImportationLicenseActive', 'the Import Permit phase'),
            'ProductVerified' => fn () => new NotYetAvailableGuard('ProductVerified', 'the Import Permit phase'),
            'IngredientNotBanned' => fn () => new NotYetAvailableGuard('IngredientNotBanned', 'the Import Permit phase'),
            'StoragePremisesSelected' => fn () => new NotYetAvailableGuard('StoragePremisesSelected', 'the Import Permit phase'),
        ];

        $this->effects = [
            'GenerateReference' => fn () => app(GenerateReference::class),
            'CreateVersionSnapshot' => fn () => app(CreateVersionSnapshot::class),
            'SetOpenKey' => fn () => new ApplicationFieldEffect('set_open_key'),
            'ClearOpenKey' => fn () => new ApplicationFieldEffect('clear_open_key'),
            'SetResumeStage' => fn () => new ApplicationFieldEffect('set_resume_stage'),
            'ClearResumeStage' => fn () => new ApplicationFieldEffect('clear_resume_stage'),
            'SetDecidedAt' => fn () => new ApplicationFieldEffect('set_decided_at'),
            'CreateAssignment' => fn () => app(CreateAssignment::class),
            'EndAssignment' => fn () => app(EndAssignment::class),
            'Notify' => fn () => app(Notify::class),
            // Later phases (D-188).
            'CreateInspections' => fn () => new NotYetAvailableEffect('CreateInspections', 'Phase 4A'),
            'UpdateInspectionDate' => fn () => new NotYetAvailableEffect('UpdateInspectionDate', 'Phase 4A'),
            'SetConductedAt' => fn () => new NotYetAvailableEffect('SetConductedAt', 'Phase 4A'),
            'SignInspection' => fn () => new NotYetAvailableEffect('SignInspection', 'Phase 4A'),
            'CancelOpenInspections' => fn () => new NotYetAvailableEffect('CancelOpenInspections', 'Phase 4A'),
            'IssuePermissionToPayLetter' => fn () => new NotYetAvailableEffect('IssuePermissionToPayLetter', 'Phase 4B'),
            'RecordPayment' => fn () => new NotYetAvailableEffect('RecordPayment', 'Phase 4B'),
            'ClearPayment' => fn () => new NotYetAvailableEffect('ClearPayment', 'Phase 4B'),
            'RejectPayment' => fn () => new NotYetAvailableEffect('RejectPayment', 'Phase 4B'),
            'IssueLicense' => fn () => new NotYetAvailableEffect('IssueLicense', 'Phase 4B'),
            'ClearAmendmentFlag' => fn () => new NotYetAvailableEffect('ClearAmendmentFlag', 'Phase 4B', [ApplicationType::Amendment]),
            'SupersedePreviousLicense' => fn () => new NotYetAvailableEffect('SupersedePreviousLicense', 'Phase 4B', $renewalOrAmendment),
            'SetLateRenewalFlag' => fn () => new NotYetAvailableEffect('SetLateRenewalFlag', 'Phase 4B', [ApplicationType::Renewal]),
            'FlagRestrictedIngredient' => fn () => new NotYetAvailableEffect('FlagRestrictedIngredient', 'the Import Permit phase'),
        ];
    }

    /**
     * Adds or replaces a guard (tests and later phases).
     *
     * @param  Closure(): Guard  $factory
     */
    public function extendGuard(string $name, Closure $factory): void
    {
        $this->guards[$name] = $factory;
    }

    /**
     * @param  Closure(): Effect  $factory
     */
    public function extendEffect(string $name, Closure $factory): void
    {
        $this->effects[$name] = $factory;
    }

    public function hasGuard(string $spec): bool
    {
        return isset($this->guards[self::split($spec)[0]]);
    }

    public function hasEffect(string $spec): bool
    {
        return isset($this->effects[self::split($spec)[0]]);
    }

    /**
     * @return array{0: Guard, 1: string|null}
     */
    public function guard(string $spec): array
    {
        [$name, $param] = self::split($spec);

        if (! isset($this->guards[$name])) {
            throw new InvalidArgumentException("Unknown workflow guard [{$name}].");
        }

        return [($this->guards[$name])(), $param];
    }

    /**
     * @return array{0: Effect, 1: string|null}
     */
    public function effect(string $spec): array
    {
        [$name, $param] = self::split($spec);

        if (! isset($this->effects[$name])) {
            throw new InvalidArgumentException("Unknown workflow effect [{$name}].");
        }

        return [($this->effects[$name])(), $param];
    }

    /**
     * `Name:param` → [Name, param].
     *
     * @return array{0: string, 1: string|null}
     */
    public static function split(string $spec): array
    {
        $parts = explode(':', $spec, 2);

        return [$parts[0], $parts[1] ?? null];
    }
}
