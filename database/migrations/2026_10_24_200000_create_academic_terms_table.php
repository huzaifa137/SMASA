<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Platform-wide terms (Term I / II / III) of an academic year, set by the
 * administrators. Exactly one term is "active" for the whole platform; the
 * billing module reads its dates and current term from here.
 *
 * Schools keep their own term_dates (their local calendar) — this table does
 * not replace it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_terms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('academic_year_id');
            $table->unsignedTinyInteger('term'); // 1, 2, 3
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->unique(['academic_year_id', 'term'], 'academic_terms_year_term_unique');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_terms');
    }
};
