<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Audit\Concerns\Audited;
use App\Domain\CompanyRegistry\Concerns\BelongsToCompany;
use App\Domain\CompanyRegistry\Enums\DocumentType;
use Carbon\CarbonImmutable;
use Database\Factories\DocumentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A typed company document with replacement history (02 §4.4, D-067).
 *
 * @property int $id
 * @property int|null $person_id
 * @property DocumentType $document_type
 * @property int $stored_file_id
 * @property string|null $document_number
 * @property CarbonImmutable|null $issued_on
 * @property CarbonImmutable|null $expires_on
 * @property int|null $replaced_by_id
 */
class Document extends Model
{
    /** @use HasFactory<DocumentFactory> */
    use Audited, BelongsToCompany, HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'person_id',
        'document_type',
        'stored_file_id',
        'document_number',
        'issued_on',
        'expires_on',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'document_type' => DocumentType::class,
            'issued_on' => 'date',
            'expires_on' => 'date',
        ];
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeCurrent(Builder $query): void
    {
        $query->whereNull('replaced_by_id');
    }

    public function isCurrent(): bool
    {
        return $this->replaced_by_id === null;
    }

    /**
     * @return BelongsTo<Person, $this>
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    /**
     * @return BelongsTo<StoredFile, $this>
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(StoredFile::class, 'stored_file_id');
    }

    /**
     * @return BelongsTo<self, $this>
     */
    public function replacedBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'replaced_by_id');
    }
}
