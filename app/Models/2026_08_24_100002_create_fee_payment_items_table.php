<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * A fee_payment can now be split across several lines instead of being
     * one lump "fees" amount:
     *   - lines tied to a fee_structure_item (the payer chose to pay
     *     Tuition, Library, etc. from the student's assigned structure), or
     *   - "external" lines that are NOT part of any fee structure at all
     *     (e.g. a broken window, a lost book fine) — these carry a category
     *     and a free-text description instead of a structure item.
     */
    public function up(): void
    {
        Schema::create('fee_payment_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('fee_payment_id');
            $table->unsignedBigInteger('fee_structure_item_id')->nullable();
            $table->unsignedBigInteger('fee_category_id')->nullable();

            $table->string('label');           // snapshot of item/category name at payment time
            $table->decimal('amount', 12, 2);
            $table->boolean('is_external')->default(false);
            $table->text('description')->nullable(); // e.g. "Broken classroom window pane"

            $table->timestamps();

            $table->foreign('fee_payment_id')
                ->references('id')->on('fee_payments')
                ->onDelete('cascade');

            $table->foreign('fee_structure_item_id')
                ->references('id')->on('fee_structure_items')
                ->nullOnDelete();

            $table->foreign('fee_category_id')
                ->references('id')->on('fee_categories')
                ->nullOnDelete();

            $table->index('fee_payment_id');
            $table->index('fee_structure_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_payment_items');
    }
};
