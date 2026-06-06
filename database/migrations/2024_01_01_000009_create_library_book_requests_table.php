<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create("library_book_requests", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("school_id");
            $table->unsignedBigInteger("member_id");
            $table->string("book_title", 255);
            $table->string("author", 150)->nullable();
            $table->string("isbn", 30)->nullable();
            $table->string("publisher", 150)->nullable();
            $table->text("reason")->nullable();
            $table->enum("status", ["pending", "approved", "rejected", "fulfilled"])->default("pending");
            $table->text("admin_notes")->nullable();
            $table->unsignedBigInteger("reviewed_by")->nullable();
            $table->timestamps();
            $table->index(["school_id", "status"]);
        });
    }

    public function down()
    {
        Schema::dropIfExists("library_book_requests");
    }
};
