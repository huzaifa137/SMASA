<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create("library_settings", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("school_id")->unique();
            $table->decimal("fine_per_day", 6, 2)->default(100.00);
            $table->integer("student_max_books")->default(3);
            $table->integer("teacher_max_books")->default(5);
            $table->integer("student_loan_days")->default(14);
            $table->integer("teacher_loan_days")->default(30);
            $table->integer("max_renewals")->default(2);
            $table->boolean("enable_reservations")->default(true);
            $table->boolean("enable_ebooks")->default(true);
            $table->boolean("enable_recommendations")->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists("library_settings");
    }
};
