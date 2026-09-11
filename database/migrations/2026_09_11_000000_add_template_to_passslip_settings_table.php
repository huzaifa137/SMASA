<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Rescopes passslip_settings from ONE saved profile per class to ONE saved
 * profile per (class, template) — so Classic/Modern/Minimal (and their
 * Nursery mirrors) each keep their own accent colour + toggles instead of
 * a class's single row being silently overwritten every time a different
 * Design Template is customised and saved for that same class.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('passslip_settings', function (Blueprint $table) {
            $table->string('template', 40)->nullable()->after('class_id');
        });

        // Backfill: every pre-existing row only ever held ONE template's
        // settings at a time anyway (whichever was last saved) — its own
        // 'template' key (if present) tells us which. Default to
        // 'classic'/'nursery-minimal' for older rows saved before the
        // template key was tracked in the JSON at all.
        DB::table('passslip_settings')->orderBy('id')->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                $decoded = json_decode($row->settings, true);
                $template = is_array($decoded) ? ($decoded['template'] ?? null) : null;

                if (!$template) {
                    $isNursery = \App\Http\Controllers\Helper::isNurseryClass($row->class_id);
                    $template = $isNursery ? 'nursery-minimal' : 'classic';
                }

                DB::table('passslip_settings')->where('id', $row->id)->update(['template' => $template]);
            }
        });

        Schema::table('passslip_settings', function (Blueprint $table) {
            $table->dropUnique(['school_id', 'class_id']);
        });

        Schema::table('passslip_settings', function (Blueprint $table) {
            $table->string('template', 40)->nullable(false)->default('classic')->change();
            $table->unique(['school_id', 'class_id', 'template']);
        });
    }

    public function down(): void
    {
        Schema::table('passslip_settings', function (Blueprint $table) {
            $table->dropUnique(['school_id', 'class_id', 'template']);
            $table->unique(['school_id', 'class_id']);
            $table->dropColumn('template');
        });
    }
};
