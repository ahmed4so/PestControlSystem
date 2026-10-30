<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * A previous password hash (02-data-model §4.1, D-082).
 */
class PasswordHistory extends Model
{
    public const UPDATED_AT = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'password_hash',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password_hash',
    ];

    /**
     * @return MorphTo<Model, $this>
     */
    public function account(): MorphTo
    {
        return $this->morphTo();
    }
}
