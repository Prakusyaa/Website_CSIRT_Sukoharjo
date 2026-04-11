<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Steps:
     *  1. Add reporter_type with a temporary default so the NOT NULL constraint is satisfiable.
     *  2. Backfill existing rows and enforce field mutual-exclusivity.
     *  3. Remove default (column stays NOT NULL but no implicit value leaks through).
     *  4. Add a database-level CHECK constraint so the invariant is always enforced.
     */
    public function up(): void
    {
        // Step 1 – add the column with a temporary default
        Schema::table('reports', function (Blueprint $table) {
            $table->enum('reporter_type', ['internal', 'external'])
                ->notNull()
                ->default('internal')
                ->after('reporter_email');
        });

        // Step 2 – backfill: rows with reporter_id → internal, otherwise → external
        //           Also null-out the field that should not contain data.
        DB::statement("
            UPDATE reports
            SET reporter_type = 'internal',
                reporter_email = NULL
            WHERE reporter_id IS NOT NULL
        ");

        DB::statement("
            UPDATE reports
            SET reporter_type = 'external',
                reporter_id = NULL
            WHERE reporter_id IS NULL
        ");

        // Step 3 – drop the temporary default
        DB::statement("ALTER TABLE reports ALTER COLUMN reporter_type DROP DEFAULT");

        // Step 4 – CHECK constraint: enforce mutual-exclusivity at the DB level
        DB::statement("
            ALTER TABLE reports
            ADD CONSTRAINT chk_reporter_type_fields CHECK (
                (reporter_type = 'internal' AND reporter_id IS NOT NULL AND reporter_email IS NULL)
                OR
                (reporter_type = 'external' AND reporter_email IS NOT NULL AND reporter_id IS NULL)
            )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            // Drop the CHECK constraint first (MySQL 8.0.16+ supports named constraints)
            $table->dropConstraint('chk_reporter_type_fields');
            $table->dropColumn('reporter_type');
        });
    }
};
