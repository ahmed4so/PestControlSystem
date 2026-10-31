<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Applications, their immutable submission versions and the typed links to
 * live company data (02-data-model §4.6, D-042, D-069, D-070).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 30)->nullable()->unique();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->string('service', 30);
            $table->string('license_category', 20)->nullable();
            $table->string('application_type', 20);
            // Foreign key added with the licenses table (Phase 4B).
            $table->unsignedBigInteger('previous_license_id')->nullable()->index();
            $table->foreignId('workflow_template_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('status', 40)->default('DRAFT')->index();
            $table->string('resume_stage', 40)->nullable();
            $table->unsignedSmallInteger('current_version_no')->default(0);
            $table->string('open_key', 80)->nullable();
            $table->boolean('is_late_renewal')->default(false);
            $table->boolean('has_restricted_ingredient')->default(false);
            $table->timestamp('first_submitted_at')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
            // Only stale drafts discarded by the System Administrator (D-193).
            $table->softDeletes();
            $table->unique(['company_id', 'open_key']);
        });

        Schema::create('application_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('version_no');
            $table->foreignId('submitted_by')->constrained('company_users')->restrictOnDelete();
            $table->timestamp('submitted_at');
            $table->string('declaration_name', 150);
            $table->string('declaration_designation', 150);
            $table->string('declaration_ip', 45);
            $table->string('declaration_user_agent', 255);
            $table->text('response_note')->nullable();
            $table->json('snapshot');
            $table->char('snapshot_hash', 64);
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['application_id', 'version_no']);
        });

        Schema::create('application_premises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->restrictOnDelete();
            $table->foreignId('premises_id')->constrained('premises')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['application_id', 'premises_id']);
        });

        Schema::create('application_persons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->restrictOnDelete();
            $table->foreignId('person_id')->constrained('persons')->restrictOnDelete();
            $table->string('role', 30)->default('TECHNICAL_PERSON');
            $table->timestamps();
            $table->unique(['application_id', 'role', 'person_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_persons');
        Schema::dropIfExists('application_premises');
        Schema::dropIfExists('application_versions');
        Schema::dropIfExists('applications');
    }
};
