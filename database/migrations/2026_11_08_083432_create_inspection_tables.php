<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Inspections (02 §4.8, procedure 6.5–6.7, D-077, D-136): one per selected
 * premises and round, each with its own Inspector. A signed report and its
 * items and media are never changed or deleted.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->restrictOnDelete();
            $table->foreignId('application_version_id')->constrained()->restrictOnDelete();
            $table->foreignId('premises_id')->constrained('premises')->restrictOnDelete();
            $table->foreignId('inspector_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('scheduled_by')->constrained('users')->restrictOnDelete();
            // Inspection round: 1, then 2, 3 … for each re-inspection (T14).
            $table->unsignedSmallInteger('round')->default(1);
            $table->date('planned_date');
            $table->dateTime('conducted_at')->nullable();
            $table->decimal('gps_lat', 10, 7)->nullable();
            $table->decimal('gps_lng', 10, 7)->nullable();
            $table->string('gps_unavailable_reason', 255)->nullable();
            $table->text('observed_facts')->nullable();
            $table->text('recommendations')->nullable();
            $table->text('deficiencies')->nullable();
            $table->string('overall_result', 30)->nullable();
            $table->string('status', 20)->default('SCHEDULED');
            $table->dateTime('signed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['application_id', 'round', 'premises_id']);
            $table->index(['inspector_id', 'status']);
        });

        Schema::create('inspection_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_id')->constrained()->restrictOnDelete();
            $table->string('item_code', 60);
            $table->string('result', 20);
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['inspection_id', 'item_code']);
        });

        Schema::create('inspection_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_id')->constrained()->restrictOnDelete();
            $table->foreignId('inspection_item_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('stored_file_id')->constrained()->restrictOnDelete();
            $table->string('media_type', 10);
            $table->decimal('gps_lat', 10, 7)->nullable();
            $table->decimal('gps_lng', 10, 7)->nullable();
            $table->dateTime('captured_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_media');
        Schema::dropIfExists('inspection_items');
        Schema::dropIfExists('inspections');
    }
};
