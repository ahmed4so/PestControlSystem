<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Federal Member States and regions for structured addresses (02 §4.2,
 * D-065).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('federal_member_states', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name_en', 100);
            $table->string('name_so', 100);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('federal_member_state_id')->constrained()->restrictOnDelete();
            $table->string('name_en', 100);
            $table->string('name_so', 100);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regions');
        Schema::dropIfExists('federal_member_states');
    }
};
