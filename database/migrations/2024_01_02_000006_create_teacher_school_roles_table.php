<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create("teacher_school_roles", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("teacher_id");
            $table->unsignedBigInteger("school_id");
            $table->unsignedBigInteger("school_role_id");
            $table->timestamps();

            $table->foreign("teacher_id")->references("id")->on("teachers")->onDelete("cascade");
            $table->foreign("school_id")->references("id")->on("schools")->onDelete("cascade");
            $table->foreign("school_role_id")->references("id")->on("school_roles")->onDelete("cascade");
            $table->unique(["teacher_id", "school_id"]);
            $table->index(["school_id", "school_role_id"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("teacher_school_roles");
    }
};
