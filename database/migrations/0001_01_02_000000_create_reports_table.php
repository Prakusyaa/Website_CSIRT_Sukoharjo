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
        Schema::create('reports', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('subject');
            $table->text('description');
            $table->integer('category_id')->nullable();
            $table->integer('severity_id')->nullable();
            $table->integer('reporter_id')->nullable();
            $table->string('reporter_email')->nullable();
            $table->integer('assigned_to')->nullable();
            $table->enum('status', ['pending', 'validated', 'in_progress', 'resolved', 'rejected', 'closed']);
            $table->integer('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('category_id')->references('id')->on('categories');
            $table->foreign('severity_id')->references('id')->on('severities');
            $table->foreign('reporter_id')->references('id')->on('users');
            $table->foreign('assigned_to')->references('id')->on('users');
            $table->foreign('created_by')->references('id')->on('users');
            
            // Helpful indexes
            $table->index('status');
            $table->index('category_id');
            $table->index('severity_id');
            $table->index('assigned_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
