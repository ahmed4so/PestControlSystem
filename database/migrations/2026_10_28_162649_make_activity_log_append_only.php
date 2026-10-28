<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Audit entries record the IP, user agent and role used (03-auth-security §13),
 * and are append-only: MySQL triggers reject UPDATE and DELETE (D-091).
 */
return new class extends Migration
{
    public function up(): void
    {
        $table = (string) config('activitylog.table_name');

        Schema::table($table, function (Blueprint $table) {
            $table->string('actor_role', 60)->nullable()->after('causer_id');
            $table->string('ip_address', 45)->nullable()->after('actor_role');
            $table->string('user_agent', 255)->nullable()->after('ip_address');
        });

        DB::unprepared("
            CREATE TRIGGER {$table}_no_update BEFORE UPDATE ON {$table}
            FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Audit log entries cannot be updated (D-091)'
        ");

        DB::unprepared("
            CREATE TRIGGER {$table}_no_delete BEFORE DELETE ON {$table}
            FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Audit log entries cannot be deleted (D-091)'
        ");
    }

    public function down(): void
    {
        $table = (string) config('activitylog.table_name');

        DB::unprepared("DROP TRIGGER IF EXISTS {$table}_no_update");
        DB::unprepared("DROP TRIGGER IF EXISTS {$table}_no_delete");

        Schema::table($table, function (Blueprint $table) {
            $table->dropColumn(['actor_role', 'ip_address', 'user_agent']);
        });
    }
};
