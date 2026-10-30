<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Premises with the facility checklist and its photos (02 §4.4, D-018,
 * D-135).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('premises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->string('label', 100);
            $table->string('premises_type', 20);
            $table->foreignId('address_fms_id')->nullable()->constrained('federal_member_states')->restrictOnDelete();
            $table->foreignId('address_region_id')->nullable()->constrained('regions')->restrictOnDelete();
            $table->string('address_district', 100)->nullable();
            $table->string('address_town_or_market', 150)->nullable();
            $table->string('address_street', 150)->nullable();
            $table->string('address_details', 255)->nullable();
            $table->decimal('gps_lat', 10, 7)->nullable();
            $table->decimal('gps_lng', 10, 7)->nullable();
            $table->string('waste_disposal_method', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

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

    public function down(): void
    {
        Schema::dropIfExists('facility_checklist_photos');
        Schema::dropIfExists('facility_checklist_items');
        Schema::dropIfExists('premises');
    }
};
