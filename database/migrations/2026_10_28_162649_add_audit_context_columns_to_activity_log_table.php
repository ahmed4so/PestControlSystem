<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit entries record the role used, the IP and the user agent
 * (03-auth-security §13). The log is append-only by application rule;
 * there are no database triggers (D-160).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table((string) config('activitylog.table_name'), function (Blueprint $table) {
            $table->string('actor_role', 60)->nullable()->after('causer_id');
            $table->string('ip_address', 45)->nullable()->after('actor_role');
            $table->string('user_agent', 255)->nullable()->after('ip_address');
        });
    }

    public function down(): void
    {
        Schema::table((string) config('activitylog.table_name'), function (Blueprint $table) {
            $table->dropColumn(['actor_role', 'ip_address', 'user_agent']);
        });
    }
};
