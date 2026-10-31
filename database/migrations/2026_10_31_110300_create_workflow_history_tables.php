<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The immutable history of every transition, review items and Technical
 * Reviewer assignments (02-data-model §4.7, D-071, D-102).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->restrictOnDelete();
            $table->foreignId('application_version_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('action', 40);
            $table->string('from_status', 40);
            $table->string('to_status', 40);
            $table->morphs('actor');
            $table->string('actor_role', 60)->nullable();
            $table->foreignId('on_behalf_of_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->text('comment')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['application_id', 'action']);
        });

        Schema::create('review_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_version_id')->constrained()->restrictOnDelete();
            $table->string('stage', 40);
            $table->string('item_code', 60);
            $table->string('result', 30);
            $table->text('comment')->nullable();
            $table->foreignId('reviewed_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('reviewed_at');
            $table->timestamps();
            $table->unique(['application_version_id', 'stage', 'item_code']);
        });

        Schema::create('stage_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->restrictOnDelete();
            $table->string('stage', 40);
            $table->foreignId('assigned_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('assigned_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('assigned_at');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
            $table->index(['application_id', 'stage', 'ended_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stage_assignments');
        Schema::dropIfExists('review_items');
        Schema::dropIfExists('workflow_actions');
    }
};
