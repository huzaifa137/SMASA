<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Supplementary invoices: bill students added AFTER a term invoice was counted,
 * without touching the original (possibly paid) invoice.
 *
 *   supplement_no      0 = the normal term invoice, 1, 2, 3... = supplementary ones
 *   parent_invoice_id  the term invoice a supplementary invoice belongs to
 *
 * The old "one invoice per school per term" key becomes
 * (term, school, supplement_no), so the database still refuses a second
 * normal invoice or a duplicated supplement number.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('billing_invoices', function (Blueprint $table) {
            $table->unsignedSmallInteger('supplement_no')->default(0)->after('sequence');
            $table->unsignedBigInteger('parent_invoice_id')->nullable()->after('supplement_no');
            $table->index('parent_invoice_id', 'billing_invoices_parent_idx');
        });

        Schema::table('billing_invoices', function (Blueprint $table) {
            $table->dropUnique('billing_invoices_term_school_unique');
            $table->unique(['billing_term_id', 'school_id', 'supplement_no'], 'billing_invoices_term_school_supp_unique');
        });
    }

    public function down(): void
    {
        // Supplementary invoices must be removed first, otherwise the old unique key cannot be restored.
        \Illuminate\Support\Facades\DB::table('billing_invoices')->where('supplement_no', '>', 0)->delete();

        Schema::table('billing_invoices', function (Blueprint $table) {
            $table->dropUnique('billing_invoices_term_school_supp_unique');
            $table->unique(['billing_term_id', 'school_id'], 'billing_invoices_term_school_unique');
        });
        Schema::table('billing_invoices', function (Blueprint $table) {
            $table->dropIndex('billing_invoices_parent_idx');
            $table->dropColumn(['supplement_no', 'parent_invoice_id']);
        });
    }
};
