<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Notifications\Enums\NotificationCode;
use App\Domain\Notifications\Enums\NotificationStatus;
use Carbon\CarbonImmutable;
use Database\Factories\NotificationLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * One email sent to an account (02-data-model §4.12, D-129). The message
 * body is never stored.
 *
 * @property int $id
 * @property string $email
 * @property NotificationCode $template
 * @property string $locale
 * @property NotificationStatus $status
 * @property string|null $provider_message_id
 * @property CarbonImmutable|null $sent_at
 * @property string|null $error
 */
class NotificationLog extends Model
{
    /** @use HasFactory<NotificationLogFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'template',
        'locale',
        'status',
        'provider_message_id',
        'sent_at',
        'error',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'template' => NotificationCode::class,
            'status' => NotificationStatus::class,
            'sent_at' => 'datetime',
        ];
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function recipient(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function related(): MorphTo
    {
        return $this->morphTo();
    }
}
