<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Applications\Enums\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One loaded version of a workflow definition (02-data-model §4.7, D-071,
 * D-097). Loaded by `workflows:load`; a loaded version never changes
 * (D-189).
 *
 * @property int $id
 * @property string $code
 * @property Service $service
 * @property int $version
 * @property bool $is_active
 * @property string $definition_hash
 */
class WorkflowTemplate extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = ['code', 'service', 'version', 'is_active', 'definition_hash'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'service' => Service::class,
            'version' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<WorkflowStage, $this>
     */
    public function stages(): HasMany
    {
        return $this->hasMany(WorkflowStage::class)->orderBy('sequence');
    }

    /**
     * @return HasMany<WorkflowTransition, $this>
     */
    public function transitions(): HasMany
    {
        return $this->hasMany(WorkflowTransition::class)->orderBy('sequence');
    }

    /**
     * The version new applications of a service start with (D-189).
     *
     * @param  Builder<self>  $query
     */
    public function scopeActiveFor(Builder $query, Service $service): void
    {
        $query->where('service', $service)->where('is_active', true)->orderByDesc('version');
    }
}
