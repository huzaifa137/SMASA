<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * School-controlled Parent Portal visibility.
 *
 * parent_portal_settings        one row per school; no row = today's behaviour
 * parent_visibility_overrides   per-student "always allow" / "hold" for results
 * parent_exam_releases          per-exam release mode (inherit the school default,
 *                               auto, manual publish, or scheduled)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parent_portal_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->unique();
            $table->boolean('show_results')->default(true);
            $table->boolean('show_attendance')->default(true);
            $table->boolean('show_fees')->default(true);
            $table->boolean('show_arrival_time')->default(true);
            $table->boolean('show_signout_time')->default(false);
            // none | any_balance | above_amount
            $table->string('fee_hold_mode', 20)->default('none');
            $table->decimal('fee_hold_amount', 14, 2)->nullable();
            // all = every fee balance of the student; exam_term = only the exam's term + year
            $table->string('fee_hold_scope', 12)->default('all');
            // auto = visible once released in the school portal (today); manual = school publishes each exam
            $table->string('results_release_mode', 10)->default('auto');
            $table->text('hold_message')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('parent_visibility_overrides', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('student_id');
            // allow = skip fee / manual-release holds; hold = block results
            $table->string('results_access', 10)->default('allow');
            $table->text('message')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'student_id'], 'parent_vis_override_unique');
        });

        Schema::create('parent_exam_releases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('examination_id');
            // auto | manual | scheduled   (no row = inherit the school default)
            $table->string('mode', 10)->default('auto');
            $table->dateTime('publish_at')->nullable();
            $table->dateTime('published_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'examination_id'], 'parent_exam_release_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_exam_releases');
        Schema::dropIfExists('parent_visibility_overrides');
        Schema::dropIfExists('parent_portal_settings');
    }
};
