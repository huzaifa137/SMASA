<?php

namespace App\Support;

use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\Route;

/**
 * The short list of pages a signed-in school user keeps for offline use.
 *
 * The list lives in config('smasa.offline_page_list'). Here it is filtered to
 * what THIS user may open (module / feature), and turned into relative URLs.
 * partials/offline-support hands the result to the site worker (public/sw.js),
 * which saves exactly these pages - visited or not - and nothing else.
 */
class OfflinePages
{
    public static function enabled(): bool
    {
        return (bool) config('smasa.offline_pages', true);
    }

    /** Teachers / school admins only: students and parents never get offline pages. */
    public static function isSchoolStaff(): bool
    {
        return session()->has('LoggedSchool')
            && (session()->has('LoggedTeacher') || session()->has('LoggedAdmin'))
            && !session()->has('LoggedStudent');
    }

    /** @return array<int, array{key:string,label:string,desc:string,icon:string,path:string}> */
    public static function forCurrentUser(): array
    {
        if (!self::enabled() || !self::isSchoolStaff()) {
            return [];
        }

        try {
            $pages = [];

            foreach ((array) config('smasa.offline_page_list', []) as $def) {
                if (empty($def['route']) || !Route::has($def['route'])) {
                    continue;
                }
                if (!empty($def['module']) && !PermissionHelper::canModule($def['module'])) {
                    continue;
                }
                if (!empty($def['feature']) && !PermissionHelper::canFeature($def['feature'])) {
                    continue;
                }

                $pages[] = [
                    'key'   => (string) $def['key'],
                    'label' => (string) $def['label'],
                    'desc'  => (string) ($def['desc'] ?? ''),
                    'icon'  => (string) ($def['icon'] ?? '📄'),
                    'path'  => route($def['route'], [], false),
                ];
            }

            return $pages;
        } catch (\Throwable $e) {
            // This runs on every page: it must never be the reason a page fails.
            report($e);

            return [];
        }
    }

    /** Changes whenever the list changes (new permission, renamed page...), so devices re-save at once. */
    public static function version(array $pages): string
    {
        return substr(md5(json_encode($pages)), 0, 12);
    }
}
