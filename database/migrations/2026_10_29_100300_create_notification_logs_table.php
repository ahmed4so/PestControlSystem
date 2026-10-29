<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per email sent (02-data-model §4.12, D-129). Bodies are never
 * stored, because some contain temporary passwords.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->morphs('recipient');
            $table->string('email', 190);
            $table->string('template', 60)->index();
            $table->string('locale', 2);
            $table->nullableMorphs('related');
            $table->string('status')->default('QUEUED')->index();
            $table->string('provider_message_id', 100)->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
