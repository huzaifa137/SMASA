<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Division bands map an AGGREGATE (sum of grade points across a
     * student's "counts toward aggregate" subjects — see
     * class_subjects.counts_towards_aggregate) to a Division label, the
     * way Uganda's PLE-style primary reporting works: e.g. 4-12 => Division
     * 1, 13-23 => Division 2, and so on.
     *
     * These bands belong to a grading_scheme, NOT the school directly,
     * because the aggregate is only meaningful in terms of the POINTS
     * that scheme's grade bands award (a D1-F9 scale needs different
     * aggregate ranges than a 1-5 scale would). A scheme with no rows
     * here simply doesn't offer Division on its pass slips — existing
     * schools/schemes are completely unaffected until they add bands.
     */
    public function up(): void
    {
        Schema::create('division_bands', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('grading_scheme_id');
            $table->integer('min_aggregate');
            $table->integer('max_aggregate');
            $table->string('division', 50);
            $table->string('remark', 100)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('grading_scheme_id')
                ->references('id')->on('grading_schemes')
                ->onDelete('cascade');

            $table->index(['grading_scheme_id', 'min_aggregate', 'max_aggregate'], 'division_bands_scheme_range_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('division_bands');
    }
};
