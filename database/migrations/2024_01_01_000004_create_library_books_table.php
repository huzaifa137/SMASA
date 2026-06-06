<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create("library_books", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("school_id");
            $table->string("title", 255);
            $table->string("isbn", 30)->nullable();
            $table->unsignedBigInteger("author_id")->nullable();
            $table->unsignedBigInteger("category_id")->nullable();
            $table->unsignedBigInteger("subject_id")->nullable();
            $table->string("publisher", 150)->nullable();
            $table->year("publication_year")->nullable();
            $table->string("edition", 50)->nullable();
            $table->string("language", 50)->default("English");
            $table->integer("total_copies")->default(1);
            $table->integer("available_copies")->default(1);
            $table->string("location", 100)->nullable();
            $table->decimal("price", 10, 2)->nullable();
            $table->text("description")->nullable();
            $table->string("cover_image")->nullable();
            $table->string("ebook_file")->nullable();
            $table->boolean("has_ebook")->default(false);
            $table->boolean("is_active")->default(true);
            $table->unsignedBigInteger("added_by")->nullable();
            $table->timestamps();
            $table->index(["school_id", "is_active"]);
            $table->index("isbn");
        });
    }

    public function down()
    {
        Schema::dropIfExists("library_books");
    }
};
