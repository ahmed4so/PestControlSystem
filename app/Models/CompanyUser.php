<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Audit\Concerns\Audited;
use App\Domain\Identity\Concerns\HasSecurityState;
use App\Domain\Identity\Enums\AccountStatus;
use App\Domain\Notifications\Actions\SendNotification;
use App\Domain\Notifications\Enums\NotificationCode;
use Carbon\CarbonImmutable;
use Database\Factories\CompanyUserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * A company account on the Company Portal (02-data-model §4.1, D-048,
 * D-062, D-063). Company accounts never share a table, guard or session
 * with staff users.
 *
 * @property int $id
 * @property int|null $company_id
 * @property string $full_name
 * @property string $email
 * @property string $phone
 * @property CarbonImmutable|null $email_verified_at
 * @property CarbonImmutable|null $two_factor_confirmed_at
 * @property string|null $pending_email
 * @property string|null $pending_email_token_hash
 * @property CarbonImmutable|null $pending_email_expires_at
 * @property CarbonImmutable $terms_accepted_at
 */
class CompanyUser extends Authenticatable
{
    /** @use HasFactory<CompanyUserFactory> */
    use Audited, HasFactory, HasSecurityState, SoftDeletes, TwoFactorAuthenticatable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'password',
        'temp_password_expires_at',
        'first_login',
        'email_verified_at',
        'status',
        'failed_login_attempts',
        'locked_until',
        'last_login_at',
        'password_changed_at',
        'pending_email',
        'pending_email_token_hash',
        'pending_email_expires_at',
        'preferred_locale',
        'terms_accepted_at',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
        'pending_email_token_hash',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'temp_password_expires_at' => 'datetime',
            'first_login' => 'boolean',
            'email_verified_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'status' => AccountStatus::class,
            'failed_login_attempts' => 'integer',
            'locked_until' => 'datetime',
            'last_login_at' => 'datetime',
            'password_changed_at' => 'datetime',
            'pending_email_expires_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
        ];
    }

    public function displayName(): string
    {
        return $this->full_name;
    }

    /**
     * Fortify's reset link, sent as PASSWORD_RESET_LINK (07 §3.1).
     */
    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        app(SendNotification::class)($this, NotificationCode::PasswordResetLink, [
            'url' => route('portal.password.reset', ['token' => $token, 'email' => $this->email]),
        ]);
    }

    /**
     * Fortify's "remember me" is disabled for both areas (D-084).
     */
    public function getRememberTokenName(): string
    {
        return '';
    }
}
