<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('account_code', 20);
            $table->string('name');
            $table->enum('type', ['asset', 'liability', 'income', 'expense', 'equity']);
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);

            // System-seeded accounts (default Chart of Accounts) are protected
            // from deletion since core Finance flows (fee payments, expenses,
            // payroll) post transactions against them automatically.
            $table->boolean('is_system')->default(false);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'account_code']);
            $table->index(['school_id', 'type']);

            $table->foreign('parent_id')
                ->references('id')
                ->on('chart_of_accounts')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};