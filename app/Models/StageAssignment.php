<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Audit\Concerns\Audited;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A Technical Reviewer assigned to an application's stage by the
 * Registrar (02-data-model §4.7, procedure 6.4).
 *
 * @property int $id
 * @property int $application_id
 * @property string $stage
 * @property int $assigned_user_id
 * @property int $assigned_by
 * @property CarbonImmutable $assigned_at
 * @property CarbonImmutable|null $ended_at
 */
class StageAssignment extends Model
{
    use Audited;

    /**
     * @var list<string>
     */
    protected $fillable = ['stage', 'assigned_user_id', 'assigned_by', 'assigned_at', 'ended_at'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeOpen(Builder $query): void
    {
        $query->whereNull('ended_at');
    }

    /**
     * @return BelongsTo<Application, $this>
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }
}
