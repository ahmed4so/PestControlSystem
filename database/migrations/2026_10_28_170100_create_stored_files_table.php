<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One uploaded or generated file in R2 (02-data-model §4.3). Rows are
 * immutable: never updated, never deleted, and the path is never reused.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stored_files', function (Blueprint $table) {
            $table->id();
            $table->string('disk', 20);
            $table->string('path', 255)->unique();
            $table->string('original_name', 255);
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->char('sha256', 64);
            $table->nullableMorphs('uploaded_by');
            $table->string('purpose', 50);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stored_files');
    }
};
