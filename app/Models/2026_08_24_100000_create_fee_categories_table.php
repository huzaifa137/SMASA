<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fee_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');

            $table->string('name');                 // e.g. "Tuition", "Tour", "Medical"
            $table->string('slug', 100);             // e.g. "tuition" - stable machine key
            $table->string('color', 10)->default('#2f2ccb');
            $table->string('icon', 50)->default('fa-tag');
            $table->text('description')->nullable();

            // "External" categories are for ad-hoc/incidental charges that are
            // not part of any fee structure (e.g. a broken item, a fine).
            // Regular categories can still be used for external payments too,
            // this flag just controls which ones are suggested first.
            $table->boolean('is_external')->default(false);

            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            $table->unique(['school_id', 'slug']);
            $table->index(['school_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_categories');
    }
};
