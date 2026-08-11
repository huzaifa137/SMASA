<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Audit trail for every merge/split done through the School Products
     * feature. Splitting is destructive (it deletes classes, streams,
     * students, marks, attendance and finance records that belong to the
     * category being dropped), so keeping a permanent record of who did
     * it, when, and how much data was affected is a minimum safety net -
     * even though the underlying rows themselves cannot be recovered.
     */
    public function up(): void
    {
        Schema::create('school_product_actions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->enum('action', ['merge', 'split']);

            // For merge: the product that was added.
            // For split: the product that was removed.
            $table->unsignedBigInteger('product_md_id');
            $table->string('product_name')->nullable();

            // For split only: the product that the school kept.
            $table->unsignedBigInteger('kept_product_md_id')->nullable();
            $table->string('kept_product_name')->nullable();

            // Snapshot of what was deleted, e.g.
            // {"classes":4,"streams":9,"students":212,"marks":1840,...}
            $table->json('impact_summary')->nullable();

            $table->unsignedBigInteger('performed_by')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'created_at']);

            $table->foreign('school_id')
                ->references('id')
                ->on('schools')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_product_actions');
    }
};