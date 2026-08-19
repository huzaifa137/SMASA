<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Report Card Template Designer — schema.
 *
 * A "template" is NOT an image. It is a JSON-described canvas of
 * positioned elements (logo, text, tables, photo, signature block, etc.)
 * that gets replayed both in the drag-and-drop builder (Fabric.js) and
 * in the final print/PDF renderer, so what you design is what prints.
 *
 * `elements` holds the CURRENT DRAFT (what the builder edits).
 * `published_elements` holds the LAST PUBLISHED version (what actually
 * gets used to print live report cards). This separation lets an admin
 * fiddle with a design without breaking report cards that are currently
 * being generated/printed by teachers.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_card_templates', function (Blueprint $table) {
            $table->id();

            // SMASA is multi-school (session('LoggedSchool')). school_id = null
            // means "starter template" shipped by the seeder and available to
            // every school; a non-null school_id is that school's own design.
            $table->unsignedBigInteger('school_id')->nullable()->index();

            $table->string('name');                     // "Sunrise Nursery — Playful Blue"
            $table->string('category');                  // nursery | primary | secondary | custom
            $table->string('description')->nullable();

            // Canvas dimensions in px at 96dpi. Default = A4 portrait (794x1123).
            $table->unsignedInteger('canvas_width')->default(794);
            $table->unsignedInteger('canvas_height')->default(1123);

            // Page background: solid color, gradient, or watermark image.
            $table->json('background')->nullable();

            // DRAFT layout — array of element objects (see docs/element-schema.md)
            $table->json('elements');

            // PUBLISHED layout — snapshot used for actual report card generation.
            $table->json('published_elements')->nullable();

            // Default template FOR THIS SCHOOL within its category. school_id
            // IS NULL + is_default = true => one of the 3 starter templates.
            // school_id IS NOT NULL + is_default = true => the template that
            // school has chosen to actually print with for that category
            // (this doubles as "school's active template", set via the
            // "Set as default" button — no extra settings table needed).
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('cloned_from')->nullable(); // parent template if duplicated

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_card_templates');
    }
};
