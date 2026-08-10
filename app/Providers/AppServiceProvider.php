<?php

namespace App\Providers;

use App\Helpers\PermissionHelper;
use App\Http\Controllers\Helper;
use App\Models\Student;
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
    }
}