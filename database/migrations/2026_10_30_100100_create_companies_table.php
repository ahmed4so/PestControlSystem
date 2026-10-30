<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Companies (02 §4.4) with the embedded address (§4.9). Most fields are
 * nullable: the profile is saved step by step and checked for
 * completeness (D-178). `owner_person_id` gets its key with `persons`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('business_registration_no', 60)->unique();
            $table->string('tax_compliance_no', 60)->nullable();
            $table->string('email', 190)->nullable();
            $table->string('phone', 30)->nullable();
            $table->foreignId('address_fms_id')->nullable()->constrained('federal_member_states')->restrictOnDelete();
            $table->foreignId('address_region_id')->nullable()->constrained('regions')->restrictOnDelete();
            $table->string('address_district', 100)->nullable();
            $table->string('address_town_or_market', 150)->nullable();
            $table->string('address_street', 150)->nullable();
            $table->string('address_details', 255)->nullable();
            $table->unsignedBigInteger('owner_person_id')->nullable();
            $table->timestamp('profile_completed_at')->nullable();
            $table->timestamp('amendment_required_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('company_users', function (Blueprint $table) {
            $table->foreign('company_id')->references('id')->on('companies')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('company_users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
        });

        Schema::dropIfExists('companies');
    }
};
