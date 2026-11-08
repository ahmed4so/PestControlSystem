<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Soft deletes for the workflow configuration (D-053). When an unused
 * version is reloaded (D-202), stages and transitions that left the
 * definition are soft deleted, never removed.
 */
return new class extends Migration
{
    private const TABLES = ['workflow_templates', 'workflow_stages', 'workflow_transitions'];

    public function up(): void
    {
        foreach (self::TABLES as $name) {
            Schema::table($name, function (Blueprint $table): void {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $name) {
            Schema::table($name, function (Blueprint $table): void {
                $table->dropSoftDeletes();
            });
        }
    }
};
