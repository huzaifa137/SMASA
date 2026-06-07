<?php

namespace App\Providers;

use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\Blade;
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
