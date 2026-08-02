<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grading_schemes', function (Blueprint $table) {
            $table->id();

            // Null school_id = a global/system default scheme every school can see & clone.
            $table->unsignedBigInteger('school_id')->nullable();

            $table->string('name');                 // e.g. "UCE Standard (100%)", "Junior School (80 Marks)"
            $table->text('description')->nullable();

            // The "scale" the exam is set out of, bundled with the scheme so that
            // picking a scheme on the exam form can auto-fill these two fields.
            $table->unsignedInteger('total_marks')->default(100); // e.g. 100 or 80
            $table->unsignedInteger('pass_mark')->default(50);    // e.g. 50 or 40

            $table->boolean('is_default')->default(false); // school's default scheme, pre-selected on create
            $table->boolean('is_active')->default(true);   // soft toggle instead of hard delete when in use

            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->unique(['school_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grading_schemes');
    }
};