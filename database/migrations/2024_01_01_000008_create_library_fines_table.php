<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create("library_fines", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("school_id");
            $table->unsignedBigInteger("borrowing_id");
            $table->unsignedBigInteger("member_id");
            $table->decimal("amount", 10, 2);
            $table->integer("overdue_days");
            $table->enum("status", ["unpaid", "paid", "waived"])->default("unpaid");
            $table->date("paid_date")->nullable();
            $table->text("waive_reason")->nullable();
            $table->unsignedBigInteger("processed_by")->nullable();
            $table->timestamps();
            $table->index(["school_id", "status"]);
            $table->index(["member_id", "status"]);
        });
    }

    public function down()
    {
        Schema::dropIfExists("library_fines");
    }
};
