<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * reports.id was created as INT PRIMARY KEY without AUTO_INCREMENT; MySQL rejects inserts with no id (SQLSTATE 1364).
     * MySQL cannot ALTER the referenced column while a foreign key exists; drop FK, alter, restore FK.
     */
    public function up(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->dropForeign(['report_id']);
        });

        DB::statement('ALTER TABLE `reports` MODIFY `id` INT NOT NULL AUTO_INCREMENT');

        Schema::table('attachments', function (Blueprint $table) {
            $table->foreign('report_id')->references('id')->on('reports');
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->dropForeign(['report_id']);
        });

        DB::statement('ALTER TABLE `reports` MODIFY `id` INT NOT NULL');

        Schema::table('attachments', function (Blueprint $table) {
            $table->foreign('report_id')->references('id')->on('reports');
        });
    }
};
