<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            Schema::table('reports', function (Blueprint $table) {
                // MySQL 8+ and SQLite ignore duplicate index errors if we catch
                $table->index('created_at');
                $table->index('reporter_id');
            });
        } catch (\Throwable $e) { }

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->index('is_active');
            });
        } catch (\Throwable $e) { }

        try {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->index('created_at');
            });
        } catch (\Throwable $e) { }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('reports', function (Blueprint $table) {
                $table->dropIndex(['created_at']);
                $table->dropIndex(['reporter_id']);
            });
        } catch (\Throwable $e) { }

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex(['is_active']);
            });
        } catch (\Throwable $e) { }

        try {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->dropIndex(['created_at']);
            });
        } catch (\Throwable $e) { }
    }
};
