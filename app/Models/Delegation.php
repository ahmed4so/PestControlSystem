<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Audit\Concerns\Audited;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Database\Factories\DelegationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * The DG delegates final decisions to a signatory for a date range
 * (02-data-model §4.1, D-027). A delegate acts only between `starts_on`
 * and `ends_on`, inclusive, and only while not revoked.
 *
 * @property int $id
 * @property int $delegator_id
 * @property int $delegate_id
 * @property CarbonImmutable $starts_on
 * @property CarbonImmutable $ends_on
 * @property string $reason
 * @property CarbonImmutable|null $revoked_at
 */
class Delegation extends Model
{
    /** @use HasFactory<DelegationFactory> */
    use Audited, HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = ['delegator_id', 'delegate_id', 'starts_on', 'ends_on', 'reason', 'created_by', 'revoked_at', 'revoked_by'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'revoked_at' => 'datetime',
        ];
    }

    /**
     * Delegations of a user that are in force on a day (the Mogadishu date).
     *
     * @param  Builder<self>  $query
     */
    public function scopeActiveFor(Builder $query, User $delegate, ?CarbonInterface $at = null): void
    {
        $day = ($at ?? now())->setTimezone((string) config('app.display_timezone'))->toDateString();

        $query->where('delegate_id', $delegate->id)
            ->whereNull('revoked_at')
            ->whereDate('starts_on', '<=', $day)
            ->whereDate('ends_on', '>=', $day);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function delegator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delegator_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function delegate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delegate_id');
    }
}
