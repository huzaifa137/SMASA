<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Links an examination to specific class+stream combinations
        Schema::create('examination_classes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('examination_id');
            $table->unsignedBigInteger('class_id');
            $table->string('stream_id')->nullable();
            $table->unsignedBigInteger('school_id');
            $table->timestamps();

            $table->foreign('examination_id')->references('id')->on('examinations')->onDelete('cascade');

            $table->unique(['examination_id', 'class_id', 'stream_id'], 'exam_class_stream_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('examination_classes');
    }
};
