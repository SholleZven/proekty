<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("CREATE EXTENSION IF NOT EXISTS pg_trgm");

        DB::statement("CREATE INDEX IF NOT EXISTS idx_products_inn ON products (inn)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_products_name_trgm ON products USING gin (name gin_trgm_ops)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_products_conclusion_result ON products (conclusion_result)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_products_functional_purpose ON products (functional_purpose)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_products_stage_construction ON products (stage_construction_works)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS idx_products_inn");
        DB::statement("DROP INDEX IF EXISTS idx_products_name_trgm");
        DB::statement("DROP INDEX IF EXISTS idx_products_conclusion_result");
        DB::statement("DROP INDEX IF EXISTS idx_products_functional_purpose");
        DB::statement("DROP INDEX IF EXISTS idx_products_stage_construction");
    }
};
