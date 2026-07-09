<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('passslip_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            // A class this saved customisation profile applies to.
            // Two or more classes (e.g. Baby Class + Middle Class) can each
            // point at the SAME settings JSON by having one row per class —
            // saving "for these classes" just upserts one row per class_id.
            $table->unsignedBigInteger('class_id');
            $table->json('settings'); // show_* toggle booleans + accent colour
            $table->timestamps();

            $table->unique(['school_id', 'class_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('passslip_settings');
    }
};