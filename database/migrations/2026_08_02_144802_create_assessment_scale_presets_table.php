<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The "system comments" a teacher can pick from for a given
     * assessment scale — e.g. score 1 / "Works under Teacher's Guidance" /
     * remark "Fair". Equivalent to grading_scales, but for comment-driven
     * scales instead of percentage grade bands.
     */
    public function up(): void
    {
        Schema::create('assessment_scale_presets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_scale_id');

            $table->decimal('score', 6, 2);
            $table->string('label');           // shown in the dropdown & auto-filled as the comment
            $table->string('remark')->nullable(); // short summary word, e.g. "Fair", "Good", "Excellent"
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->foreign('assessment_scale_id')->references('id')->on('assessment_scales')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_scale_presets');
    }
};
