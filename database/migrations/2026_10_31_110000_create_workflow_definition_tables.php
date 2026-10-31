<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Workflow configuration (02-data-model §4.7, 04 §13, D-071, D-097):
 * loaded from config/workflows by `workflows:load`, never edited by hand.
 * A loaded version never changes (D-189); `definition_hash` detects a
 * changed file for an already loaded version.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_templates', function (Blueprint $table) {
            $table->id();
            $table->string('code', 60);
            $table->string('service', 30);
            $table->unsignedSmallInteger('version');
            $table->boolean('is_active')->default(true);
            $table->char('definition_hash', 64);
            $table->timestamps();
            $table->unique(['code', 'version']);
            $table->index(['service', 'is_active']);
        });

        Schema::create('workflow_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_template_id')->constrained()->restrictOnDelete();
            $table->string('code', 40);
            $table->string('name_en', 100);
            $table->string('name_so', 100);
            $table->unsignedSmallInteger('sequence');
            $table->string('actor_role', 40);
            $table->string('company_status', 60);
            $table->boolean('is_final')->default(false);
            $table->json('review_items')->nullable();
            $table->timestamps();
            $table->unique(['workflow_template_id', 'code']);
        });

        Schema::create('workflow_transitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_template_id')->constrained()->restrictOnDelete();
            $table->string('from_stage', 40);
            $table->string('action', 40);
            $table->string('actor_role', 40);
            // A plain target, or conditional targets in order with a default (04 §13).
            $table->string('to_stage', 40)->nullable();
            $table->json('to_conditions')->nullable();
            $table->boolean('requires_comment')->default(false);
            $table->boolean('requires_review_items')->default(false);
            $table->boolean('requires_password')->default(false);
            $table->json('guards');
            $table->json('effects');
            $table->unsignedSmallInteger('sequence');
            $table->timestamps();
            $table->unique(['workflow_template_id', 'from_stage', 'action'], 'workflow_transitions_template_from_action_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_transitions');
        Schema::dropIfExists('workflow_stages');
        Schema::dropIfExists('workflow_templates');
    }
};
