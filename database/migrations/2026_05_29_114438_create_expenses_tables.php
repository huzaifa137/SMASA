<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Expense Categories
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');

            $table->string('name'); // e.g. Salaries, Utilities
            $table->string('color', 10)->default('#6366f1');
            $table->string('icon', 50)->default('fa-receipt');

            $table->text('description')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });

        // Expenses
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();

            $table->string('expense_number', 30)->unique();

            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('category_id');

            $table->string('title');
            $table->text('description')->nullable();

            $table->decimal('amount', 12, 2);

            $table->date('expense_date');

            $table->string('academic_year', 10);
            $table->tinyInteger('term')->nullable();

            $table->enum('payment_method', [
                'cash',
                'bank_transfer',
                'mobile_money',
                'cheque',
                'other'
            ])->default('cash');

            $table->string('payee_name')->nullable();

            $table->string('transaction_reference')->nullable();

            $table->string('receipt_attachment')->nullable();

            $table->enum('status', [
                'draft',
                'approved',
                'paid',
                'cancelled'
            ])->default('paid');

            $table->unsignedBigInteger('submitted_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();

            $table->timestamp('approved_at')->nullable();

            $table->text('approval_notes')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['school_id', 'academic_year', 'term']);
            $table->index(['school_id', 'category_id']);

            // Foreign Keys
            $table->foreign('category_id')
                  ->references('id')
                  ->on('expense_categories')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
    }
};