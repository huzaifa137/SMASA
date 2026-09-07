<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Some schools follow the strict UNEB PLE convention where failing
     * even one aggregate subject makes the whole result Ungraded,
     * regardless of how low the aggregate number itself is (three
     * distinctions and one outright fail can still sum to a low,
     * "good-looking" aggregate). Others don't want that rule at all and
     * would rather the aggregate number speak for itself.
     *
     * Defaults to true (the stricter/standard behaviour) so schools that
     * already relied on the old hardcoded "any fail => Ungraded" logic
     * see no change; it's just now a per-scheme switch instead of being
     * baked into the controller.
     */
    public function up(): void
    {
        Schema::table('grading_schemes', function (Blueprint $table) {
            $table->boolean('ungraded_on_fail')->default(true)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('grading_schemes', function (Blueprint $table) {
            $table->dropColumn('ungraded_on_fail');
        });
    }
};
