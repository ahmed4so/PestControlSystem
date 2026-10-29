<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A yearly counter per prefix (02-data-model §4.2). Only the NumberSequencer
 * changes these rows.
 */
class NumberSequence extends Model
{
    protected $fillable = [
        'prefix',
        'year',
        'last_number',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'last_number' => 'integer',
        ];
    }
}
