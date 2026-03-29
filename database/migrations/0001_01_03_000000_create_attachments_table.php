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
        Schema::create('attachments', function (Blueprint $table) {
            $table->integer('id', true)->primary();
            $table->integer('report_id');
            $table->string('file_name')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_type')->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->integer('createdby')->nullable(); // Exact match to requested schema
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('report_id')->references('id')->on('reports');
            $table->foreign('createdby')->references('id')->on('users');

            // Indexes
            $table->index('report_id');
            $table->index('createdby');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
