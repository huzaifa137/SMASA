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
        Schema::table('fee_structure_items', function (Blueprint $table) {
            // Nullable + kept alongside the existing 'category' string column on
            // purpose: old rows created before this migration only have the
            // string, so nothing breaks. New/edited items get both — the FK for
            // proper per-school category management, the string kept in sync as
            // a denormalized label for fast display and old reports/exports.
            $table->unsignedBigInteger('fee_category_id')->nullable()->after('category');

            $table->foreign('fee_category_id')
                ->references('id')->on('fee_categories')
                ->nullOnDelete();

            $table->index('fee_category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fee_structure_items', function (Blueprint $table) {
            $table->dropForeign(['fee_category_id']);
            $table->dropIndex(['fee_category_id']);
            $table->dropColumn('fee_category_id');
        });
    }
};
