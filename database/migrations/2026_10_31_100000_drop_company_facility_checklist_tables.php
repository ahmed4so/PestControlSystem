<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The facility checklist is the Inspector's job, not the company's (D-192).
 * The company checklist tables are dropped; the stored photo files stay in
 * the file store (StoredFile is immutable). The Inspector's checklist comes
 * with the inspection report (Phase 4A).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('facility_checklist_photos');
        Schema::dropIfExists('facility_checklist_items');
    }

    public function down(): void
    {
        Schema::create('facility_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('premises_id')->constrained('premises')->restrictOnDelete();
            $table->string('item_code', 40);
            $table->boolean('is_available')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['premises_id', 'item_code']);
        });

        Schema::create('facility_checklist_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_checklist_item_id')->constrained()->restrictOnDelete();
            $table->foreignId('stored_file_id')->constrained()->restrictOnDelete();
            $table->timestamp('captured_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
};
