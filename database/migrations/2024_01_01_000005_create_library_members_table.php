<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create("library_members", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("school_id");
            $table->string("member_type", 20);
            $table->unsignedBigInteger("member_id");
            $table->string("library_card_number", 30)->unique();
            $table->date("membership_date");
            $table->date("expiry_date")->nullable();
            $table->integer("max_books_allowed")->default(3);
            $table->integer("max_days_allowed")->default(14);
            $table->enum("status", ["active", "suspended", "expired"])->default("active");
            $table->text("suspension_reason")->nullable();
            $table->unsignedBigInteger("added_by")->nullable();
            $table->timestamps();
            $table->index(["school_id", "member_type", "member_id"]);
            $table->index("library_card_number");
        });
    }

    public function down()
    {
        Schema::dropIfExists("library_members");
    }
};
