<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * SaaS BILLING module — SMASA (platform) bills each school once per term.
 *
 *   billing_settings      one row: default rate per student, tax, due days,
 *                         company details and payment instructions
 *   billing_terms         one row per (academic year, term). Exactly one is
 *                         "current". Holds the rate charged per student
 *   billing_school_rates  per-school price overrides / discounts / exemptions
 *   billing_invoices      one invoice per school per billing term
 *   billing_invoice_items invoice lines (student charge + adjustments)
 *   billing_payments      payments received against an invoice
 *   billing_logs          who did what (audit trail for the module)
 *
 * Plain unsignedBigInteger columns and no foreign keys, matching the rest of
 * this codebase (schools / students tables are not FK-constrained either).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('default_rate_per_student', 12, 2)->default(1000);
            $table->string('currency', 8)->default('UGX');
            $table->string('tax_name', 30)->default('VAT');
            $table->decimal('tax_rate', 5, 2)->default(18);
            $table->boolean('tax_enabled')->default(true);
            $table->unsignedSmallInteger('due_days')->default(30);
            $table->string('invoice_prefix', 12)->default('INV');
            $table->string('company_name', 150)->default('SMASA');
            $table->string('company_tagline', 190)->nullable();
            $table->string('company_address', 255)->nullable();
            $table->string('company_phone', 60)->nullable();
            $table->string('company_email', 120)->nullable();
            $table->string('company_website', 120)->nullable();
            $table->string('company_tin', 60)->nullable();
            $table->text('payment_instructions')->nullable();
            $table->text('invoice_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('billing_terms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->string('year_label', 60);
            $table->unsignedTinyInteger('term'); // 1, 2, 3
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('due_date')->nullable();
            $table->decimal('rate_per_student', 12, 2);
            $table->boolean('is_current')->default(false);
            $table->string('status', 12)->default('open'); // open | closed
            $table->text('notes')->nullable();
            $table->timestamp('invoices_generated_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique(['year_label', 'term'], 'billing_terms_year_term_unique');
            $table->index('is_current');
        });

        Schema::create('billing_school_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('billing_term_id')->nullable(); // null = every term
            $table->decimal('custom_rate', 12, 2)->nullable();          // null = use the term rate
            $table->string('discount_type', 10)->default('none');      // none | percent | fixed
            $table->decimal('discount_value', 12, 2)->default(0);
            $table->boolean('is_exempt')->default(false);               // not billed at all
            $table->string('reason', 255)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'billing_term_id']);
        });

        Schema::create('billing_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 40)->unique();
            $table->unsignedBigInteger('billing_term_id');
            $table->unsignedBigInteger('school_id');
            $table->unsignedInteger('sequence')->default(0);

            $table->unsignedInteger('student_count')->default(0);
            $table->decimal('rate_per_student', 12, 2)->default(0);

            $table->decimal('subtotal', 14, 2)->default(0);
            $table->string('discount_type', 10)->default('none');
            $table->decimal('discount_value', 12, 2)->default(0);
            $table->string('discount_reason', 255)->nullable();
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->string('tax_name', 30)->nullable();
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->decimal('amount_paid', 14, 2)->default(0);
            $table->decimal('balance', 14, 2)->default(0);

            // draft | issued | partial | paid | cancelled | waived
            $table->string('status', 12)->default('draft');
            $table->date('issue_date')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('counted_at')->nullable(); // when student_count was last taken
            $table->text('notes')->nullable();
            $table->string('cancel_reason', 255)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique(['billing_term_id', 'school_id'], 'billing_invoices_term_school_unique');
            $table->index('school_id');
            $table->index('status');
            $table->index('due_date');
        });

        Schema::create('billing_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->string('type', 12)->default('students'); // students | charge | credit
            $table->string('description', 255);
            $table->decimal('quantity', 12, 2)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('amount', 14, 2)->default(0); // negative for credits
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('invoice_id');
        });

        Schema::create('billing_payments', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number', 40)->unique();
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('billing_term_id');
            $table->unsignedBigInteger('school_id');
            $table->decimal('amount', 14, 2);
            $table->string('method', 20)->default('bank');     // bank | mobile_money | cash | cheque | other
            $table->string('reference', 120)->nullable();
            $table->date('paid_on');
            $table->text('notes')->nullable();
            $table->string('status', 10)->default('valid');   // valid | reversed
            $table->timestamp('reversed_at')->nullable();
            $table->string('reversed_reason', 255)->nullable();
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->timestamps();

            $table->index('invoice_id');
            $table->index(['school_id', 'billing_term_id']);
            $table->index('paid_on');
        });

        Schema::create('billing_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('billing_term_id')->nullable();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->string('action', 40);
            $table->string('description', 500);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->index('invoice_id');
            $table->index('billing_term_id');
        });

        // Seed the single settings row so the module works out of the box.
        DB::table('billing_settings')->insert([
            'default_rate_per_student' => 1000,
            'currency'                 => 'UGX',
            'tax_name'                 => 'VAT',
            'tax_rate'                 => 18,
            'tax_enabled'              => true,
            'due_days'                 => 30,
            'invoice_prefix'           => 'INV',
            'company_name'             => 'SMASA',
            'company_tagline'          => 'School Management System',
            'payment_instructions'     => "Bank: ____________________\nAccount name: ____________________\nAccount number: ____________________\nMobile Money: ____________________\n\nPlease quote the invoice number as the payment reference.",
            'invoice_notes'            => 'Charges are calculated per enrolled student for the term. Thank you for choosing SMASA.',
            'created_at'               => now(),
            'updated_at'               => now(),
        ]);
    }

    public function down(): void
    {
        foreach ([
            'billing_logs', 'billing_payments', 'billing_invoice_items', 'billing_invoices',
            'billing_school_rates', 'billing_terms', 'billing_settings',
        ] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
