<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create("role_module_access", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("school_role_id");
            $table->unsignedBigInteger("module_id");
            $table->boolean("can_access")->default(true);
            $table->timestamps();

            $table->foreign("school_role_id")->references("id")->on("school_roles")->onDelete("cascade");
            $table->foreign("module_id")->references("id")->on("system_modules")->onDelete("cascade");
            $table->unique(["school_role_id", "module_id"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("role_module_access");
    }
};
