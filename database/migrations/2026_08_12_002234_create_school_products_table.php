<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A school used to belong to exactly one "School Product" category
     * (schools.school_product, a single master_datas.md_id under the
     * SCHOOL_PRODUCTS master code). Some schools now need to operate
     * under more than one category at once (a "merge"), so this table
     * turns that relationship into a proper many-to-many.
     *
     * schools.school_product is intentionally left in place and is kept
     * in sync as the school's "primary" product (see School::syncPrimaryProduct()):
     *  - every other part of the app that still reads $school->school_product
     *    directly (reports, exports, the school profile page, etc.) keeps
     *    working unmodified.
     *  - it also gives us a sane single value to fall back to if the
     *    pivot table is ever empty for a school.
     */
    public function up(): void
    {
        Schema::create('school_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');

            // References master_datas.md_id (master code SCHOOL_PRODUCTS).
            $table->unsignedBigInteger('product_md_id');

            // The product the school started with / the one shown anywhere
            // that still only supports a single product. Exactly one row
            // per school should have this set to true; enforced in code
            // (SchoolProductMergeService) rather than at the DB level
            // because MySQL can't do a partial-unique-index in a portable way.
            $table->boolean('is_primary')->default(false);

            $table->unsignedBigInteger('added_by')->nullable();
            $table->timestamps();

            $table->unique(
                ['school_id', 'product_md_id'],
                'school_products_unique'
            );

            $table->index('school_id');

            $table->foreign('school_id')
                ->references('id')
                ->on('schools')
                ->onDelete('cascade');
        });

        // Backfill: every existing school already has exactly one product
        // in schools.school_product. Carry that over as its primary row so
        // behaviour is 100% unchanged for every school until someone
        // explicitly merges a second category in.
        $schools = DB::table('schools')
            ->whereNotNull('school_product')
            ->get(['id', 'school_product']);

        $now = now();
        $rows = [];

        foreach ($schools as $school) {
            if ($school->school_product === null || $school->school_product === '') {
                continue;
            }

            $rows[] = [
                'school_id' => $school->id,
                'product_md_id' => $school->school_product,
                'is_primary' => true,
                'added_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('school_products')->insert($chunk);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('school_products');
    }
};