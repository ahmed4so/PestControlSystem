<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Applications\Enums\ApplicationType;
use App\Domain\Applications\Enums\LicenseCategory;
use App\Domain\Applications\Enums\Service;
use App\Domain\Audit\Concerns\Audited;
use App\Domain\CompanyRegistry\Concerns\BelongsToCompany;
use Carbon\CarbonImmutable;
use Database\Factories\ApplicationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * An application for one service (02-data-model §4.6). Its status changes
 * only through WorkflowEngine::perform() (D-096); it is never deleted,
 * except a stale draft discarded by the System Administrator (D-193).
 *
 * @property int $id
 * @property string|null $reference
 * @property int $company_id
 * @property Service $service
 * @property LicenseCategory|null $license_category
 * @property ApplicationType $application_type
 * @property int|null $previous_license_id
 * @property int|null $workflow_template_id
 * @property ApplicationStatus $status
 * @property string|null $resume_stage
 * @property int $current_version_no
 * @property string|null $open_key
 * @property bool $is_late_renewal
 * @property bool $has_restricted_ingredient
 * @property CarbonImmutable|null $first_submitted_at
 * @property CarbonImmutable|null $decided_at
 * @property CarbonImmutable|null $updated_at
 */
class Application extends Model
{
    /** @use HasFactory<ApplicationFactory> */
    use Audited, BelongsToCompany, HasFactory, SoftDeletes;

    /**
     * Only what a company chooses on its draft. Status, reference, version,
     * keys and dates are set by the workflow engine and its effects.
     *
     * @var list<string>
     */
    protected $fillable = ['service', 'license_category', 'application_type', 'previous_license_id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'service' => Service::class,
            'license_category' => LicenseCategory::class,
            'application_type' => ApplicationType::class,
            'status' => ApplicationStatus::class,
            'current_version_no' => 'integer',
            'is_late_renewal' => 'boolean',
            'has_restricted_ingredient' => 'boolean',
            'first_submitted_at' => 'datetime',
            'decided_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<WorkflowTemplate, $this>
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(WorkflowTemplate::class, 'workflow_template_id');
    }

    /**
     * @return HasMany<ApplicationVersion, $this>
     */
    public function versions(): HasMany
    {
        return $this->hasMany(ApplicationVersion::class)->orderBy('version_no');
    }

    public function currentVersion(): ?ApplicationVersion
    {
        return $this->current_version_no === 0
            ? null
            : $this->versions()->where('version_no', $this->current_version_no)->first();
    }

    /**
     * Selected premises (live data; the submitted values are in the snapshot).
     *
     * @return BelongsToMany<Premises, $this>
     */
    public function premises(): BelongsToMany
    {
        return $this->belongsToMany(Premises::class, 'application_premises')->withTimestamps();
    }

    /**
     * Selected technical person(s) (D-070).
     *
     * @return BelongsToMany<Person, $this>
     */
    public function technicalPersons(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'application_persons')
            ->wherePivot('role', 'TECHNICAL_PERSON')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * @return HasMany<WorkflowAction, $this>
     */
    public function actions(): HasMany
    {
        return $this->hasMany(WorkflowAction::class)->orderBy('id');
    }

    /**
     * @return HasMany<StageAssignment, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(StageAssignment::class);
    }
}
