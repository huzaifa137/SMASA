<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Uganda's PLE-style aggregate is only ever summed over a fixed
     * handful of "examinable" subjects per class (classically English,
     * Mathematics, Science, Social Studies) — everything else a school
     * teaches (e.g. Religious Education) is real, graded, reported on the
     * slip, but must NOT be added into the aggregate/Division.
     *
     * This flag lives on class_subjects (not the shared subject catalog)
     * because it's a per-school, per-class decision: what counts toward
     * the aggregate at P.7 might differ from P.4, and one school's naming
     * of "SST" vs "Social Studies" shouldn't force every school into the
     * same four subjects.
     *
     * Backfill: rather than leave every existing school's slips showing
     * no aggregate at all until someone visits a new settings page, seed
     * a sensible default from subject name — the four standard PLE
     * subjects, matched case-insensitively against both the shared
     * master-data name and any school's own custom subject name. Schools
     * are free to adjust this per class from Examinations → Aggregate
     * Subjects afterwards; this only sets a reasonable starting point.
     */
    public function up(): void
    {
        Schema::table('class_subjects', function (Blueprint $table) {
            $table->boolean('counts_towards_aggregate')->default(false)->after('subject_type');
        });

        $patterns = ['english', 'mathematics', 'math', 'science', 'social studies', 'sst'];

        // Master-data-backed subjects (the common case).
        $masterIds = DB::table('master_datas')
            ->where(function ($q) use ($patterns) {
                foreach ($patterns as $p) {
                    $q->orWhere('md_name', 'like', "%{$p}%");
                }
            })
            ->pluck('md_id');

        if ($masterIds->isNotEmpty()) {
            DB::table('class_subjects')
                ->where('subject_source', 'master')
                ->whereIn('subject_id', $masterIds)
                ->update(['counts_towards_aggregate' => true]);
        }

        // Schools running their own custom subject list.
        if (Schema::hasTable('custom_subjects')) {
            $customIds = DB::table('custom_subjects')
                ->where(function ($q) use ($patterns) {
                    foreach ($patterns as $p) {
                        $q->orWhere('subject_name', 'like', "%{$p}%");
                    }
                })
                ->pluck('id');

            if ($customIds->isNotEmpty()) {
                DB::table('class_subjects')
                    ->where('subject_source', 'custom')
                    ->whereIn('custom_subject_id', $customIds)
                    ->update(['counts_towards_aggregate' => true]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('class_subjects', function (Blueprint $table) {
            $table->dropColumn('counts_towards_aggregate');
        });
    }
};
