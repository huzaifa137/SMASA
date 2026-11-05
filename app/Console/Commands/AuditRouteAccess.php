<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

/**
 * php artisan smasa:audit-routes
 * php artisan smasa:audit-routes --all        (also list routes without parameters)
 *
 * Lists the routes that take a record id (e.g. /exam/results/{examId}) and
 * which middleware protects them, flagging the risky pattern behind past
 * cross-school bugs:
 *
 *   OPEN-TO-ANY-STAFF   only 'AdminAuth', which lets school staff in as well as
 *                       platform admins, so the CONTROLLER must check the school
 *                       (SchoolContext::abortUnlessOwns / school_id) itself.
 *   SOFT-GUARD          admin.guard:* only constrains admin sessions; school
 *                       sessions pass straight through to the controller.
 *
 * Re-run after adding routes and review every flagged line.
 */
class AuditRouteAccess extends Command
{
    protected $signature = 'smasa:audit-routes {--all : Include routes without parameters}';

    protected $description = 'List id-taking routes and flag the ones any logged-in staff member can reach';

    public function handle(): int
    {
        $strict = ['admin.can', 'admin.module', 'admin.super', 'module', 'SchoolAuth', 'ParentAuth', 'secondary.school'];
        $rows = [];

        foreach (Route::getRoutes() as $route) {
            $uri = $route->uri();

            if (!$this->option('all') && !str_contains($uri, '{')) {
                continue;
            }

            if (str_starts_with($uri, '_') || str_starts_with($uri, 'dev/') || str_contains($uri, 'sanctum')) {
                continue;
            }

            $middleware = collect($route->gatherMiddleware())->filter(fn($m) => is_string($m));
            $names = $middleware->map(fn($m) => explode(':', $m)[0]);

            $hasStrict = $names->intersect($strict)->isNotEmpty();
            $soft = $names->contains('admin.guard');
            $adminAuth = $names->contains('AdminAuth');

            $flag = '';
            if ($adminAuth && !$hasStrict) {
                $flag = $soft ? 'SOFT-GUARD' : 'OPEN-TO-ANY-STAFF';
            } elseif ($soft && !$hasStrict) {
                $flag = 'SOFT-GUARD';
            } elseif (!$adminAuth && !$hasStrict && $names->intersect(['web', 'localized'])->isNotEmpty() && str_contains($uri, '{')) {
                $flag = 'NO-AUTH-MIDDLEWARE';
            }

            $rows[] = [
                $flag,
                implode('|', $route->methods()[0] === 'GET' ? ['GET'] : array_diff($route->methods(), ['HEAD'])),
                '/' . ltrim($uri, '/'),
                ltrim(str_replace('App\\Http\\Controllers\\', '', $route->getActionName()), '\\'),
            ];
        }

        usort($rows, fn($a, $b) => [$b[0] !== '', $a[2]] <=> [$a[0] !== '', $b[2]]);

        $this->table(['Flag', 'Method', 'URI', 'Action'], $rows);

        $flagged = collect($rows)->filter(fn($r) => $r[0] !== '')->count();
        $this->line('');
        $this->info(count($rows) . ' route(s) listed, ' . $flagged . ' flagged. Each flagged route must check the school inside its controller.');

        return self::SUCCESS;
    }
}
