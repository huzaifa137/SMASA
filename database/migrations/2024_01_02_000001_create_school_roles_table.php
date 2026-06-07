<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create("school_roles", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("school_id");
            $table->string("name");
            $table->string("description")->nullable();
            $table->boolean("is_active")->default(true);
            $table->timestamps();

            $table->foreign("school_id")->references("id")->on("schools")->onDelete("cascade");
            $table->unique(["school_id", "name"]);
            $table->index(["school_id", "is_active"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("school_roles");
    }
};
