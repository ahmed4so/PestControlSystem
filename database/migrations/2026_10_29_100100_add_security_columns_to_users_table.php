<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Staff security columns (02-data-model §4.1, D-081, D-082, D-125) and the
 * 24-hour temporary password expiry (D-083, D-164).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name', 150)->change();
            $table->string('email', 190)->change(); // unique index is kept
            $table->timestamp('temp_password_expires_at')->nullable()->after('password');
            $table->unsignedInteger('failed_login_attempts')->default(0)->after('first_login');
            $table->timestamp('locked_until')->nullable()->after('failed_login_attempts');
            $table->timestamp('last_login_at')->nullable()->after('locked_until');
            $table->timestamp('password_changed_at')->nullable()->after('last_login_at');
            $table->string('preferred_locale', 2)->default('en')->after('password_changed_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'temp_password_expires_at',
                'failed_login_attempts',
                'locked_until',
                'last_login_at',
                'password_changed_at',
                'preferred_locale',
            ]);
            $table->string('name', 255)->change();
            $table->string('email', 255)->change();
        });
    }
};
