<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Passport is removed (D-049). The earlier oauth_* migrations are kept unchanged,
 * so this migration drops their tables.
 */
return new class extends Migration
{
    private const TABLES = [
        'oauth_device_codes',
        'oauth_refresh_tokens',
        'oauth_access_tokens',
        'oauth_auth_codes',
        'oauth_clients',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            Schema::dropIfExists($table);
        }
    }

    public function down(): void
    {
        // Passport is not coming back; nothing to restore.
    }
};
