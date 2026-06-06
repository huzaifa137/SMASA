<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create("library_borrowings", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("school_id");
            $table->unsignedBigInteger("book_id");
            $table->unsignedBigInteger("member_id");
            $table->string("borrow_number", 20)->unique();
            $table->date("borrow_date");
            $table->date("due_date");
            $table->date("return_date")->nullable();
            $table->integer("renewals")->default(0);
            $table->enum("status", ["borrowed", "returned", "overdue", "lost"])->default("borrowed");
            $table->text("notes")->nullable();
            $table->unsignedBigInteger("issued_by")->nullable();
            $table->unsignedBigInteger("returned_to")->nullable();
            $table->timestamps();
            $table->index(["school_id", "status"]);
            $table->index(["book_id", "status"]);
            $table->index(["member_id", "status"]);
        });
    }

    public function down()
    {
        Schema::dropIfExists("library_borrowings");
    }
};
