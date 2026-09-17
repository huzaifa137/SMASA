<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A-Level principal/subsidiary subjects are chosen per STUDENT (see
 * StudentALevelCombination / ALevelCombinationController), not per class
 * — so before this, a subject a student was actually taking (e.g.
 * Physics as one of their 3 principals) never showed up in
 * class_subjects at all, meaning no teacher could ever be assigned to
 * teach it (Class/attached-stream-subjects.blade.php only ever lists
 * class_subjects rows).
 *
 * is_auto_synced marks a row this sync created FROM combinations, so the
 * prune step (removing a subject nobody in the class/stream is taking
 * anymore) only ever touches rows it created itself — General Paper
 * (added at class-creation, compulsory regardless of any student's
 * combination) and anything a school manually ticked on the
 * class-creation subject picker are never auto-added or auto-removed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_subjects', function (Blueprint $table) {
            $table->boolean('is_auto_synced')->default(false)->after('subject_type');
        });
    }

    public function down(): void
    {
        Schema::table('class_subjects', function (Blueprint $table) {
            $table->dropColumn('is_auto_synced');
        });
    }
};
