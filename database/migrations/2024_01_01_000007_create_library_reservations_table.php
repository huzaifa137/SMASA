<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create("library_reservations", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("school_id");
            $table->unsignedBigInteger("book_id");
            $table->unsignedBigInteger("member_id");
            $table->string("reservation_number", 20)->unique();
            $table->date("reservation_date");
            $table->date("expiry_date")->nullable();
            $table->enum("status", ["pending", "ready", "fulfilled", "cancelled", "expired"])->default("pending");
            $table->text("notes")->nullable();
            $table->timestamps();
            $table->index(["school_id", "status"]);
            $table->index(["book_id", "member_id"]);
        });
    }

    public function down()
    {
        Schema::dropIfExists("library_reservations");
    }
};
