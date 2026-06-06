<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create("library_authors", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("school_id");
            $table->string("name", 150);
            $table->text("bio")->nullable();
            $table->string("nationality", 80)->nullable();
            $table->boolean("is_active")->default(true);
            $table->timestamps();
            $table->index("school_id");
        });
    }

    public function down()
    {
        Schema::dropIfExists("library_authors");
    }
};
