<?php

namespace App\Providers;

use App\Helpers\NumberHelper;
use App\Helpers\PermissionHelper;
use App\Http\Controllers\Helper;
use App\Models\Student;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register PermissionHelper as a singleton so it's easy to resolve
        $this->app->singleton('permission', function () {
            return new PermissionHelper();
        });
    }

    public function boot()
    {
        $this->registerLookupInvalidation();
        $this->registerRateLimiters();

        // ── Standard pagination design ──────────────────────────────
        // Every ->links() / ->render() call in the system now renders the
        // same pager used on /students/all-students (see
        // resources/views/pagination/smasa.blade.php) unless a view name
        // is passed explicitly.
        Paginator::defaultView('pagination.smasa');
        Paginator::defaultSimpleView('pagination.smasa-simple');

        // ── Parent portal sidebar ───────────────────────────────────
        // Every "parents.*" view (dashboard, results, attendance,
        // finance, child-overview) shares the same sidebar shell, so the
        // list of children this parent actually has access to is
        // resolved once here instead of being re-queried in every
        // controller action.
        View::composer('parents.*', function ($view) {
            if (!session('ParentId')) {
                return;
            }

            $phone = session('ParentPhone');

            $sidebarChildren = Student::where('primary_contact', $phone)
                ->orderBy('firstname')
                ->get()
                ->map(function ($student) {
                    $student->school_name = Helper::schoolNameBySchoolID($student->school_id);
                    return $student;
                })
                ->groupBy('school_id');

            $view->with([
                'sidebarChildren' => $sidebarChildren,
                'sidebarActiveStudentId' => request()->route('id'),
            ]);
        });

        // ── Blade Directives ────────────────────────────────────────

        /**
         * @canmodule('finance')  ... @endcanmodule
         * Wraps content that should only show if the current user
         * has access to the given module.
         */
        Blade::directive('canmodule', function ($module) {
            return "<?php if(\\App\\Helpers\\PermissionHelper::canModule({$module})): ?>";
        });
        Blade::directive('endcanmodule', function () {
            return "<?php endif; ?>";
        });

        /**
         * @canfeature('add_student')  ... @endcanfeature
         */
        Blade::directive('canfeature', function ($feature) {
            return "<?php if(\\App\\Helpers\\PermissionHelper::canFeature({$feature})): ?>";
        });
        Blade::directive('endcanfeature', function () {
            return "<?php endif; ?>";
        });

        /**
         * @cannotmodule('finance')  ... @endcannotmodule
         */
        Blade::directive('cannotmodule', function ($module) {
            return "<?php if(!\\App\\Helpers\\PermissionHelper::canModule({$module})): ?>";
        });
        Blade::directive('endcannotmodule', function () {
            return "<?php endif; ?>";
        });

        /**
         * @cannotfeature('delete_student')  ... @endcannotfeature
         */
        Blade::directive('cannotfeature', function ($feature) {
            return "<?php if(!\\App\\Helpers\\PermissionHelper::canFeature({$feature})): ?>";
        });
        Blade::directive('endcannotfeature', function () {
            return "<?php endif; ?>";
        });

        /**
         * @whole($value)
         * Prints a mark/percentage/total as a whole number, dropping
         * any decimal places ("93.00" -> "93") instead of every pass
         * slip and report export printing the full decimal. Null/blank
         * values print the same em-dash placeholder used everywhere
         * else a mark can be missing.
         */
        Blade::directive('whole', function ($expression) {
            return "<?php echo \\App\\Helpers\\NumberHelper::whole({$expression}); ?>";
        });
    }

    /**
     * Keep App\Support\LookupCache honest: any INSERT/UPDATE/DELETE on a
     * table behind a cached lookup (master data, school names, academic
     * years, pass-slip settings) flushes that group, wherever the write
     * comes from (controller, model, seeder, command).
     */
    private function registerLookupInvalidation(): void
    {
        \Illuminate\Support\Facades\DB::listen(function ($query) {
            $sql = $query->sql;
            $head = strtolower(substr(ltrim($sql), 0, 6));

            if ($head !== 'insert' && $head !== 'update' && $head !== 'delete') {
                return;
            }

            foreach (\App\Support\LookupCache::TABLE_GROUPS as $table => $group) {
                if (stripos($sql, '`' . $table . '`') !== false) {
                    \App\Support\LookupCache::flush($group);
                }
            }
        });

        // Workers keep PHP alive between jobs: never reuse a previous job's memo.
        \Illuminate\Support\Facades\Queue::before(fn() => \App\Support\LookupCache::forgetMemo());
    }

    /**
     * Named limits used by routes as throttle:login / throttle:password-reset /
     * throttle:scan. Login limits are per IP (generous: a whole school can sit
     * behind one address) AND per account name (tight: stops password guessing).
     */
    private function registerRateLimiters(): void
    {
        \Illuminate\Support\Facades\RateLimiter::for('login', function (\Illuminate\Http\Request $request) {
            $who = strtolower(trim((string) ($request->input('email') ?? $request->input('username') ?? $request->input('phone') ?? '')));

            return [
                \Illuminate\Cache\RateLimiting\Limit::perMinute(60)->by('login-ip|' . $request->ip()),
                \Illuminate\Cache\RateLimiting\Limit::perMinute(10)->by('login-id|' . $request->ip() . '|' . $who),
            ];
        });

        \Illuminate\Support\Facades\RateLimiter::for('password-reset', function (\Illuminate\Http\Request $request) {
            return [
                \Illuminate\Cache\RateLimiting\Limit::perMinute(10)->by('reset-ip|' . $request->ip()),
                \Illuminate\Cache\RateLimiting\Limit::perHour(10)->by('reset-id|' . strtolower((string) ($request->input('email') ?? $request->input('phone') ?? $request->ip()))),
            ];
        });

        // A gate scans about one card a second at its busiest; this only stops a runaway client.
        \Illuminate\Support\Facades\RateLimiter::for('scan', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(240)
                ->by('scan|' . (session('LoggedSchool') ?? 'x') . '|' . $request->ip());
        });
    }
}