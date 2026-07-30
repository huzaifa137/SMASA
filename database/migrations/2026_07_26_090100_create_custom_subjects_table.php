<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Brand new table. Nothing existing reads or writes to it yet, so this
     * migration is 100% safe to run on the live server at any time.
     */
    public function up(): void
    {
        Schema::create('custom_subjects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');

            // Mirrors the existing subject_type/class_type buckets already
            // used in class_subjects ('idaad', 'thanawi', 'primary_theology',
            // 'primary_secular') so the rest of the system's grouping logic
            // keeps working unchanged.
            $table->string('class_type');

            $table->string('subject_name');
            $table->string('subject_code')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['school_id', 'class_type']);
            $table->unique(['school_id', 'class_type', 'subject_name'], 'custom_subjects_unique_per_school_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_subjects');
    }
};
