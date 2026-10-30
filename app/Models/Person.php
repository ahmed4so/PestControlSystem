<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Audit\Concerns\Audited;
use App\Domain\Audit\Support\Mask;
use App\Domain\CompanyRegistry\Concerns\BelongsToCompany;
use App\Domain\CompanyRegistry\Concerns\HasAddress;
use Carbon\CarbonImmutable;
use Database\Factories\PersonFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * An Owner/MD or technical person, owned by one company (02 §4.4). The
 * national ID is encrypted at rest and unique across the whole system
 * while the person is active (D-066, D-088).
 *
 * @property int $id
 * @property string $full_name
 * @property string|null $nationality
 * @property string $national_id_no
 * @property string $national_id_hash
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $qualification
 * @property string|null $institution
 * @property int|null $years_of_experience
 * @property string|null $training_pcp_handling
 * @property string|null $training_fumigation
 * @property CarbonImmutable|null $released_at
 * @property int|null $released_by
 * @property string|null $release_reason
 */
class Person extends Model
{
    /** @use HasFactory<PersonFactory> */
    use Audited, BelongsToCompany, HasAddress, HasFactory, SoftDeletes;

    protected $table = 'persons';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'full_name',
        'nationality',
        'national_id_no',
        'email',
        'phone',
        'address_fms_id',
        'address_region_id',
        'address_district',
        'address_town_or_market',
        'address_street',
        'address_details',
        'qualification',
        'institution',
        'years_of_experience',
        'training_pcp_handling',
        'training_fumigation',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'national_id_no',
        'national_id_hash',
        'active_nid_key',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'national_id_no' => 'encrypted',
            'years_of_experience' => 'integer',
            'released_at' => 'datetime',
        ];
    }

    /**
     * The national ID is hidden, so it is never written to the audit log;
     * masking is a second guard (D-091).
     *
     * @return list<string>
     */
    public function auditMaskedAttributes(): array
    {
        return ['national_id_no'];
    }

    public function maskedNationalId(): string
    {
        return (string) Mask::value($this->national_id_no);
    }

    public function isReleased(): bool
    {
        return $this->released_at !== null;
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function releasedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by');
    }

    /**
     * @return HasMany<Document, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
