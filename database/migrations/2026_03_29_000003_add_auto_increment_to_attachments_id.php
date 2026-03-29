<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * attachments.id was INT PRIMARY KEY without AUTO_INCREMENT (MySQL 1364 on insert).
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE `attachments` MODIFY `id` INT NOT NULL AUTO_INCREMENT');
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE `attachments` MODIFY `id` INT NOT NULL');
    }
};
