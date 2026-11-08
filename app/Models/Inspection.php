<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Audit\Concerns\Audited;
use App\Domain\Inspection\Enums\InspectionStatus;
use App\Domain\Inspection\Enums\OverallResult;
use Carbon\CarbonImmutable;
use Database\Factories\InspectionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use LogicException;

/**
 * One inspection of one selected premises in one round (02 §4.8, D-077).
 * Signing locks the report: a signed inspection, its items and its media
 * never change (procedure 6.7). Every change is audited.
 *
 * @property int $id
 * @property int $application_id
 * @property int $application_version_id
 * @property int $premises_id
 * @property int $inspector_id
 * @property int $scheduled_by
 * @property int $round
 * @property CarbonImmutable $planned_date
 * @property CarbonImmutable|null $conducted_at
 * @property string|null $gps_lat
 * @property string|null $gps_lng
 * @property string|null $gps_unavailable_reason
 * @property string|null $observed_facts
 * @property string|null $recommendations
 * @property string|null $deficiencies
 * @property OverallResult|null $overall_result
 * @property InspectionStatus $status
 * @property CarbonImmutable|null $signed_at
 */
class Inspection extends Model
{
    /** @use HasFactory<InspectionFactory> */
    use Audited, HasFactory, SoftDeletes;

    /**
     * The report fields an Inspector fills in. Scheduling, the visit and
     * signing are set by the workflow effects.
     *
     * @var list<string>
     */
    protected $fillable = [
        'gps_lat', 'gps_lng', 'gps_unavailable_reason',
        'observed_facts', 'recommendations', 'deficiencies', 'overall_result',
    ];

    protected static function booted(): void
    {
        static::updating(function (Inspection $inspection): void {
            if ($inspection->getOriginal('signed_at') !== null) {
                throw new LogicException('A signed inspection report cannot be changed.');
            }
        });
        static::deleting(fn () => throw new LogicException('Inspections are never deleted.'));
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'planned_date' => 'date',
            'conducted_at' => 'datetime',
            'signed_at' => 'datetime',
            'status' => InspectionStatus::class,
            'overall_result' => OverallResult::class,
            'round' => 'integer',
        ];
    }

    public function isSigned(): bool
    {
        return $this->signed_at !== null;
    }

    /**
     * @param  Builder<Inspection>  $query
     */
    public function scopeOpen(Builder $query): void
    {
        $query->where('status', InspectionStatus::Scheduled)->whereNull('signed_at');
    }

    /**
     * @return BelongsTo<Application, $this>
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * @return BelongsTo<Premises, $this>
     */
    public function premises(): BelongsTo
    {
        return $this->belongsTo(Premises::class)->withoutGlobalScope('company');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    /**
     * @return HasMany<InspectionItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(InspectionItem::class);
    }

    /**
     * @return HasMany<InspectionMedia, $this>
     */
    public function media(): HasMany
    {
        return $this->hasMany(InspectionMedia::class);
    }
}
