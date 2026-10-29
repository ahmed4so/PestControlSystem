<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Staff users are deactivated, never deleted (D-053): status becomes the
 * ACTIVE / INACTIVE enum (02-data-model §4.1) and users get soft deletes.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->where('status', 'active')->update(['status' => 'ACTIVE']);
        DB::table('users')->whereNotIn('status', ['ACTIVE'])->update(['status' => 'INACTIVE']);

        Schema::table('users', function (Blueprint $table) {
            $table->string('status')->default('ACTIVE')->change();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->string('status')->default('active')->change();
        });

        DB::table('users')->where('status', 'ACTIVE')->update(['status' => 'active']);
        DB::table('users')->where('status', 'INACTIVE')->update(['status' => 'suspended']);
    }
};
