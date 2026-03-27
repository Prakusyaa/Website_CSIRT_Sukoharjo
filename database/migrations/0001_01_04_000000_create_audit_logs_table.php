<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('user_id')->nullable();
            $table->enum('action', ['created', 'updated', 'deleted'])->nullable();
            $table->string('table_name');
            $table->integer('record_id');
            $table->json('changes');
            $table->timestamp('created_at')->nullable();

            $table->foreign('user_id')->references('id')->on('users');
            
            // Indexes
            $table->index(['table_name', 'record_id']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
