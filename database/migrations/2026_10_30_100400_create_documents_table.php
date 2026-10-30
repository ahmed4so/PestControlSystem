<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Typed company documents with replacement history (02 §4.4, D-067). The
 * current document of a type has `replaced_by_id` null.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->foreignId('person_id')->nullable()->constrained('persons')->restrictOnDelete();
            $table->string('document_type', 50);
            $table->foreignId('stored_file_id')->constrained()->restrictOnDelete();
            $table->string('document_number', 60)->nullable();
            $table->date('issued_on')->nullable();
            $table->date('expires_on')->nullable();
            $table->foreignId('replaced_by_id')->nullable()->constrained('documents')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['company_id', 'document_type', 'replaced_by_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
