<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create("library_categories", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("school_id");
            $table->string("name", 100);
            $table->string("slug", 120)->nullable();
            $table->text("description")->nullable();
            $table->string("color", 20)->default("#5351e4");
            $table->boolean("is_active")->default(true);
            $table->timestamps();
            $table->index(["school_id", "is_active"]);
        });
    }

    public function down()
    {
        Schema::dropIfExists("library_categories");
    }
};
