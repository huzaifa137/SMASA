<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create("module_features", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("module_id");
            $table->string("key");
            $table->string("name");
            $table->text("description")->nullable();
            $table->integer("sort_order")->default(0);
            $table->timestamps();

            $table->foreign("module_id")->references("id")->on("system_modules")->onDelete("cascade");
            $table->unique(["module_id", "key"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("module_features");
    }
};
