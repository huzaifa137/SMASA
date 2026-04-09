<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grading_scales', function (Blueprint $table) {
            $table->id();
            $table->string('grade');          // A, B+, B, C, D, F
            $table->decimal('min_mark', 5, 2);
            $table->decimal('max_mark', 5, 2);
            $table->string('remark');         // Distinction, Excellent, Good, etc.
            $table->decimal('points', 4, 2);  // GPA equivalent
            $table->unsignedBigInteger('school_id')->nullable(); // null = global default
            $table->timestamps();
        });

        // Seed default Uganda-style grading (O-Level / A-Level compatible)
        DB::table('grading_scales')->insert([
            ['grade' => 'D1',  'min_mark' => 80, 'max_mark' => 100, 'remark' => 'Distinction',       'points' => 1, 'school_id' => null, 'created_at' => now(), 'updated_at' => now()],
            ['grade' => 'D2',  'min_mark' => 75, 'max_mark' => 79,  'remark' => 'Distinction',       'points' => 2, 'school_id' => null, 'created_at' => now(), 'updated_at' => now()],
            ['grade' => 'C3',  'min_mark' => 70, 'max_mark' => 74,  'remark' => 'Credit',            'points' => 3, 'school_id' => null, 'created_at' => now(), 'updated_at' => now()],
            ['grade' => 'C4',  'min_mark' => 65, 'max_mark' => 69,  'remark' => 'Credit',            'points' => 4, 'school_id' => null, 'created_at' => now(), 'updated_at' => now()],
            ['grade' => 'C5',  'min_mark' => 60, 'max_mark' => 64,  'remark' => 'Credit',            'points' => 5, 'school_id' => null, 'created_at' => now(), 'updated_at' => now()],
            ['grade' => 'C6',  'min_mark' => 55, 'max_mark' => 59,  'remark' => 'Credit',            'points' => 6, 'school_id' => null, 'created_at' => now(), 'updated_at' => now()],
            ['grade' => 'P7',  'min_mark' => 45, 'max_mark' => 54,  'remark' => 'Pass',              'points' => 7, 'school_id' => null, 'created_at' => now(), 'updated_at' => now()],
            ['grade' => 'P8',  'min_mark' => 40, 'max_mark' => 44,  'remark' => 'Pass',              'points' => 8, 'school_id' => null, 'created_at' => now(), 'updated_at' => now()],
            ['grade' => 'F9',  'min_mark' => 0,  'max_mark' => 39,  'remark' => 'Fail',              'points' => 9, 'school_id' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('grading_scales');
    }
};
