<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Persons owned by one company (02 §4.4). The national ID is encrypted;
 * `active_nid_key` equals the HMAC hash while the person is neither
 * released nor deleted, and its unique index enforces D-066 in the
 * database.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('persons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->string('full_name', 150);
            $table->char('nationality', 2)->nullable();
            $table->text('national_id_no');
            $table->char('national_id_hash', 64)->index();
            $table->string('email', 190)->nullable();
            $table->string('phone', 30)->nullable();
            $table->foreignId('address_fms_id')->nullable()->constrained('federal_member_states')->restrictOnDelete();
            $table->foreignId('address_region_id')->nullable()->constrained('regions')->restrictOnDelete();
            $table->string('address_district', 100)->nullable();
            $table->string('address_town_or_market', 150)->nullable();
            $table->string('address_street', 150)->nullable();
            $table->string('address_details', 255)->nullable();
            $table->string('qualification', 200)->nullable();
            $table->string('institution', 200)->nullable();
            $table->unsignedTinyInteger('years_of_experience')->nullable();
            $table->text('training_pcp_handling')->nullable();
            $table->text('training_fumigation')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->foreignId('released_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->text('release_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->char('active_nid_key', 64)
                ->nullable()
                ->storedAs('IF(released_at IS NULL AND deleted_at IS NULL, national_id_hash, NULL)')
                ->unique();
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->foreign('owner_person_id')->references('id')->on('persons')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign(['owner_person_id']);
        });

        Schema::dropIfExists('persons');
    }
};
