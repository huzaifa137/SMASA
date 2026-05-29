<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeeStructureItemsTable extends Migration
{
    public function up()
    {
        Schema::create('fee_structure_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fee_structure_id');
            $table->string('item_name');                  // e.g. "Tuition", "Lunch", "Library"
            $table->string('category');                   // tuition, boarding, activities, other
            $table->decimal('amount', 12, 2);
            $table->boolean('is_mandatory')->default(true);
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('fee_structure_id')->references('id')->on('fee_structures')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('fee_structure_items');
    }
}