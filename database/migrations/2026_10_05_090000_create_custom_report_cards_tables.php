<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Custom (per-school) report cards.
 *
 *  - custom_report_templates      : registry of bespoke report-card designs.
 *                                   The design itself is a Blade file in
 *                                   resources/views/Examination/passslips/custom/<slug>.blade.php;
 *                                   this row only carries the display name,
 *                                   the level it is for and an on/off switch.
 *
 *  - school_custom_report_cards   : which design a school has been assigned
 *                                   for each level (Nursery / Primary /
 *                                   Secondary). One row per school + level.
 *                                   Assigned by a platform admin only.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('custom_report_templates')) {
            Schema::create('custom_report_templates', function (Blueprint $table) {
                $table->id();
                // Doubles as the Blade file name AND as the suffix of the
                // template key saved in passslip_settings ("custom-<slug>"),
                // which is a varchar(40) -> keep the slug <= 30 chars.
                $table->string('slug', 30)->unique();
                $table->string('name', 120);
                $table->string('level', 20)->default('primary'); // nursery | primary | secondary
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->index('level');
            });
        }

        if (!Schema::hasTable('school_custom_report_cards')) {
            Schema::create('school_custom_report_cards', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('school_id');
                $table->foreignId('custom_report_template_id')
                    ->constrained('custom_report_templates')
                    ->restrictOnDelete();
                $table->string('level', 20); // nursery | primary | secondary
                $table->boolean('is_active')->default(true);
                // true  = the school only ever sees its own design.
                // false = it is the default, but the school may still switch
                //         to the standard Classic/Modern/Minimal designs.
                $table->boolean('lock_to_custom')->default(true);
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('assigned_by')->nullable();
                $table->timestamp('assigned_at')->nullable();
                $table->timestamps();

                $table->unique(['school_id', 'level']);
                $table->index('school_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('school_custom_report_cards');
        Schema::dropIfExists('custom_report_templates');
    }
};
