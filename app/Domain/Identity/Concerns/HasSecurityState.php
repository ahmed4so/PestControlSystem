<?php

declare(strict_types=1);

namespace App\Domain\Identity\Concerns;

use App\Domain\Identity\Enums\AccountStatus;
use App\Domain\Identity\Enums\AuthArea;
use App\Models\PasswordHistory;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Account-security state shared by staff and company accounts: status,
 * lockout (D-081), temporary passwords (D-063, D-083) and password expiry
 * (D-082). Columns are listed in 02-data-model §4.1.
 *
 * @property AccountStatus $status
 * @property bool $first_login
 * @property int $failed_login_attempts
 * @property CarbonImmutable|null $locked_until
 * @property CarbonImmutable|null $temp_password_expires_at
 * @property CarbonImmutable|null $password_changed_at
 * @property CarbonImmutable|null $last_login_at
 * @property string $preferred_locale
 */
trait HasSecurityState
{
    abstract public function displayName(): string;

    public function area(): AuthArea
    {
        return AuthArea::forAccount($this);
    }

    public function isActive(): bool
    {
        return $this->status === AccountStatus::Active;
    }

    public function isLocked(): bool
    {
        return $this->locked_until !== null && $this->locked_until->isFuture();
    }

    public function hasExpiredTemporaryPassword(): bool
    {
        return $this->temp_password_expires_at !== null && $this->temp_password_expires_at->isPast();
    }

    public function passwordExpiresAt(): ?CarbonImmutable
    {
        return $this->password_changed_at?->copy()->addDays((int) config('identity.password.expires_after_days'));
    }

    /**
     * True on first login and once the password is older than 90 days. An
     * account without a recorded change date must also set a password.
     */
    public function mustChangePassword(): bool
    {
        if ($this->first_login) {
            return true;
        }

        $expiresAt = $this->passwordExpiresAt();

        return $expiresAt === null || $expiresAt->isPast();
    }

    public function preferredLocale(): string
    {
        return in_array($this->preferred_locale, ['en', 'so'], true) ? $this->preferred_locale : 'en';
    }

    /**
     * @return MorphMany<PasswordHistory, $this>
     */
    public function passwordHistories(): MorphMany
    {
        return $this->morphMany(PasswordHistory::class, 'account');
    }
}
