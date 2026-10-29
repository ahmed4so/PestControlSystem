<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Company accounts (02-data-model §4.1, D-048, D-062, D-063). Kept apart
 * from staff `users`; `company_id` gets its foreign key in Phase 2, when
 * the `companies` table exists.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->unique();
            $table->string('full_name', 150);
            $table->string('email', 190)->unique();
            $table->string('phone', 30);
            $table->string('password');
            $table->timestamp('temp_password_expires_at')->nullable();
            $table->boolean('first_login')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->string('status')->default('ACTIVE');
            $table->unsignedInteger('failed_login_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('password_changed_at')->nullable();
            $table->string('pending_email', 190)->nullable();
            $table->string('pending_email_token_hash', 64)->nullable();
            $table->timestamp('pending_email_expires_at')->nullable();
            $table->string('preferred_locale', 2)->default('en');
            $table->timestamp('terms_accepted_at');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('company_user_password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Company sessions live in their own table, so ending one realm's
        // sessions can never touch the other's (D-048).
        Schema::create('company_sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_sessions');
        Schema::dropIfExists('company_user_password_reset_tokens');
        Schema::dropIfExists('company_users');
    }
};
