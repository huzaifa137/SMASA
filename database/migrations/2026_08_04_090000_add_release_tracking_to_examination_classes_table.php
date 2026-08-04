<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Results release used to be all-or-nothing at the examination level
     * (Examination::status = 'results_released'), which forced every class
     * to wait on the slowest one. This lets a single class+stream be
     * released the moment ITS marks are complete, independent of the rest
     * of the exam — the exam-level status is still the final fallback/
     * bulk action, but a class row here takes priority when present.
     */
    public function up(): void
    {
        Schema::table('examination_classes', function (Blueprint $table) {
            $table->timestamp('results_released_at')->nullable()->after('school_id');
            $table->unsignedBigInteger('released_by')->nullable()->after('results_released_at');
        });
    }

    public function down(): void
    {
        Schema::table('examination_classes', function (Blueprint $table) {
            $table->dropColumn(['results_released_at', 'released_by']);
        });
    }
};
