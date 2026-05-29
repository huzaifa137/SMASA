<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('title');
            $table->string('academic_year', 10);
            $table->tinyInteger('term')->nullable();
            $table->decimal('total_income_budget', 12, 2)->default(0);
            $table->decimal('total_expense_budget', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'approved', 'closed'])->default('draft');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamps();
        });

        Schema::create('budget_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('budget_id');
            $table->string('item_name');
            $table->enum('type', ['income', 'expense']);
            $table->unsignedBigInteger('category_id')->nullable();
            $table->decimal('budgeted_amount', 12, 2);
            $table->decimal('actual_amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('budget_id')
                ->references('id')
                ->on('budgets')
                ->onDelete('cascade');
        });

        Schema::create('finance_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('reference_number', 40)->unique();
            $table->enum('type', ['income', 'expense', 'refund', 'transfer']);
            $table->string('source_type');
            $table->unsignedBigInteger('source_id')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('description');
            $table->date('transaction_date');
            $table->string('academic_year', 10);
            $table->tinyInteger('term')->nullable();
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'academic_year', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_transactions');
        Schema::dropIfExists('budget_items');
        Schema::dropIfExists('budgets');
    }
};