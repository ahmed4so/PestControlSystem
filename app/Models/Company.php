<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Audit\Concerns\Audited;
use App\Domain\CompanyRegistry\Concerns\HasAddress;
use App\Domain\Identity\Enums\AuthArea;
use Carbon\CarbonImmutable;
use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A company and its profile (02 §4.4). On the Company Portal an account
 * only ever sees its own company (03 §11.3).
 *
 * @property int $id
 * @property string $name
 * @property string $business_registration_no
 * @property string|null $tax_compliance_no
 * @property string|null $email
 * @property string|null $phone
 * @property int|null $owner_person_id
 * @property CarbonImmutable|null $profile_completed_at
 * @property CarbonImmutable|null $amendment_required_at
 */
class Company extends Model
{
    /** @use HasFactory<CompanyFactory> */
    use Audited, HasAddress, HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'business_registration_no',
        'tax_compliance_no',
        'email',
        'phone',
        'address_fms_id',
        'address_region_id',
        'address_district',
        'address_town_or_market',
        'address_street',
        'address_details',
        'owner_person_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'address_fms_id' => 'integer',
            'address_region_id' => 'integer',
            'owner_person_id' => 'integer',
            'profile_completed_at' => 'datetime',
            'amendment_required_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // On the Company Portal an account sees only its own company.
        static::addGlobalScope('company', function (Builder $query): void {
            if (AuthArea::current() !== AuthArea::Company) {
                return;
            }

            $account = auth('company')->user();
            $query->whereKey($account instanceof CompanyUser ? ($account->company_id ?? 0) : 0);
        });
    }

    /**
     * @return HasOne<CompanyUser, $this>
     */
    public function account(): HasOne
    {
        return $this->hasOne(CompanyUser::class);
    }

    /**
     * @return BelongsTo<Person, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'owner_person_id');
    }

    /**
     * @return HasMany<Person, $this>
     */
    public function persons(): HasMany
    {
        return $this->hasMany(Person::class);
    }

    /**
     * @return HasMany<Premises, $this>
     */
    public function premises(): HasMany
    {
        return $this->hasMany(Premises::class);
    }

    /**
     * @return HasMany<Document, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * @return HasMany<Document, $this>
     */
    public function currentDocuments(): HasMany
    {
        return $this->documents()->whereNull('replaced_by_id');
    }
}
