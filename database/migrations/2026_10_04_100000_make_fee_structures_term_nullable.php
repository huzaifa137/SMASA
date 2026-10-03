<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A fee structure's term is now optional: NULL means "applies to every
     * term". A student's fee allocation still always belongs to ONE concrete
     * term (payments, balances and reports are all per-term), so an all-terms
     * structure is expanded into a real term when it is allocated.
     */
    public function up(): void
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->tinyInteger('term')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Any all-terms structure has to be given a real term before the
        // column can go back to NOT NULL.
        \Illuminate\Support\Facades\DB::table('fee_structures')->whereNull('term')->update(['term' => 1]);

        Schema::table('fee_structures', function (Blueprint $table) {
            $table->tinyInteger('term')->nullable(false)->change();
        });
    }
};
