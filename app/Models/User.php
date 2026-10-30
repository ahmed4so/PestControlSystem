<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Audit\Concerns\Audited;
use App\Domain\Identity\Concerns\HasSecurityState;
use App\Domain\Identity\Enums\AccountStatus;
use App\Domain\Notifications\Actions\SendNotification;
use App\Domain\Notifications\Enums\NotificationCode;
use Carbon\CarbonImmutable;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

/**
 * A SARIS staff member on the Admin Portal (02-data-model §4.1, D-048).
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property CarbonImmutable|null $email_verified_at
 * @property CarbonImmutable|null $two_factor_confirmed_at
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use Audited, HasFactory, HasRoles, HasSecurityState, Notifiable, SoftDeletes, TwoFactorAuthenticatable;

    // Staff roles and permissions are seeded for the 'web' guard (D-048).
    protected string $guard_name = 'web';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'status',
        'first_login',
        'temp_password_expires_at',
        'failed_login_attempts',
        'locked_until',
        'last_login_at',
        'password_changed_at',
        'preferred_locale',
        'email_verified_at',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'first_login' => 'boolean',
            'status' => AccountStatus::class,
            'temp_password_expires_at' => 'datetime',
            'failed_login_attempts' => 'integer',
            'locked_until' => 'datetime',
            'last_login_at' => 'datetime',
            'password_changed_at' => 'datetime',
        ];
    }

    public function displayName(): string
    {
        return $this->name;
    }

    /**
     * Fortify's reset link, sent as PASSWORD_RESET_LINK (07 §3.1).
     */
    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        app(SendNotification::class)($this, NotificationCode::PasswordResetLink, [
            'url' => route('admin.password.reset', ['token' => $token, 'email' => $this->email]),
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
