<?php

namespace App\Support;

use App\Http\Controllers\Helper;
use App\Models\CustomReportTemplate;
use App\Models\SchoolCustomReportCard;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

/**
 * Registry + resolver for CUSTOM (per-school) report cards.
 *
 * A custom report card is a Blade file living in
 *
 *      resources/views/Examination/passslips/custom/<slug>.blade.php
 *
 * which starts with a metadata comment, e.g.
 *
 *      {{--
 *        @report-card
 *        name: St. Example Primary
 *        level: primary
 *        description: Navy letterhead, two-column marks table
 *        accent: #1e3a8a
 *        progressive: true      (optional - load the Progressive Assessment Record)
 *        toggles: show_logo, show_photo, show_qr, show_remarks
 *        off_by_default: show_stu_house
 *      --}}
 *
 * Files whose name starts with "_" are shared partials and are never
 * listed as designs. The admin screen "Custom Report Cards" syncs those
 * files into the custom_report_templates table and assigns them to
 * schools (school_custom_report_cards, one row per school + level).
 *
 * The built-in Classic / Modern / Minimal / Nursery / Secondary designs are
 * untouched: a school with NO active assignment for a level keeps using them.
 */
class CustomReportCards
{
    public const LEVELS = [
        'nursery' => 'Nursery',
        'primary' => 'Primary',
        'secondary' => 'Secondary (O / A-Level)',
    ];

    /** passslip_settings.template is varchar(40): "custom-" + <=30 char slug. */
    public const KEY_PREFIX = 'custom-';
    public const MAX_SLUG = 30;

    /** @var array<string, object|null> per-request memo of assignment lookups */
    private static array $assignmentCache = [];

    /** @var array<string, array|null> per-request memo of parsed file headers */
    private static array $metaCache = [];

    // ─── Keys, paths, names ─────────────────────────────────────────────────

    public static function viewDir(): string
    {
        return resource_path('views/Examination/passslips/custom');
    }

    public static function viewName(string $slug): string
    {
        return 'Examination.passslips.custom.' . $slug;
    }

    public static function filePath(string $slug): string
    {
        return self::viewDir() . DIRECTORY_SEPARATOR . $slug . '.blade.php';
    }

    public static function templateKey(string $slug): string
    {
        return self::KEY_PREFIX . $slug;
    }

    public static function isCustomTemplateKey(?string $template): bool
    {
        return str_starts_with((string) $template, self::KEY_PREFIX);
    }

    public static function slugFromKey(?string $template): ?string
    {
        return self::isCustomTemplateKey($template)
            ? substr((string) $template, strlen(self::KEY_PREFIX))
            : null;
    }

    public static function isValidSlug(string $slug): bool
    {
        return strlen($slug) <= self::MAX_SLUG
            && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) === 1;
    }

    /**
     * Which of the three levels a class belongs to. Same single source of
     * truth the built-in templates already use (Helper::isNurseryClass /
     * isSecondaryClass); anything else is Primary by elimination.
     */
    public static function levelForClass($classId): string
    {
        if (Helper::isNurseryClass($classId)) {
            return 'nursery';
        }

        return Helper::isSecondaryClass($classId) ? 'secondary' : 'primary';
    }

    /**
     * Which level a ?template= key belongs to when it is a built-in key.
     */
    public static function levelForBuiltinKey(?string $template): string
    {
        $template = (string) $template;

        if (str_starts_with($template, 'nursery-')) {
            return 'nursery';
        }
        if (str_starts_with($template, 'secondary-')) {
            return 'secondary';
        }

        return 'primary';
    }

    // ─── Design files on disk ───────────────────────────────────────────────

    /**
     * Parse the "{{-- @report-card ... --}}" header of a design file into a
     * normalised metadata array. Unknown keys are ignored.
     */
    public static function parseHeader(string $contents, string $slug): array
    {
        $raw = [];

        if (preg_match('/\{\{--\s*@report-card\b(.*?)--\}\}/s', $contents, $m)) {
            foreach (preg_split('/\R/', trim($m[1])) as $line) {
                if (preg_match('/^\s*([a-z_]+)\s*:\s*(.*?)\s*$/i', $line, $kv)) {
                    $raw[strtolower($kv[1])] = $kv[2];
                }
            }
        }

        $level = strtolower($raw['level'] ?? 'primary');
        if (!isset(self::LEVELS[$level])) {
            $level = 'primary';
        }

        $accent = $raw['accent'] ?? null;
        if ($accent !== null && !preg_match('/^#[0-9A-Fa-f]{6}$/', $accent)) {
            $accent = null;
        }

        $registry = array_keys(Helper::passslipToggleRegistry());
        $csv = fn(?string $v): array => array_values(array_filter(array_map('trim', explode(',', (string) $v))));

        $toggles = array_values(array_intersect($csv($raw['toggles'] ?? ''), $registry));
        $off = array_values(array_intersect($csv($raw['off_by_default'] ?? ''), $toggles));

        return [
            'slug' => $slug,
            'name' => $raw['name'] ?? Str::headline($slug),
            'level' => $level,
            'description' => $raw['description'] ?? null,
            'accent' => $accent,
            // "progressive: true" asks the system to also load the Progressive
            // Assessment Record (every sitting of the term) for this design.
            'progressive' => in_array(strtolower($raw['progressive'] ?? ''), ['1', 'true', 'yes'], true),
            'toggles' => $toggles,
            'off_by_default' => $off,
            'has_header' => !empty($raw),
        ];
    }

    /**
     * Metadata for one design file, or null if the file does not exist.
     */
    public static function readMeta(string $slug): ?array
    {
        if (!self::isValidSlug($slug)) {
            return null;
        }

        if (array_key_exists($slug, self::$metaCache)) {
            return self::$metaCache[$slug];
        }

        $path = self::filePath($slug);
        if (!is_file($path)) {
            return self::$metaCache[$slug] = null;
        }

        return self::$metaCache[$slug] = self::parseHeader((string) file_get_contents($path), $slug);
    }

    /**
     * Every design file currently on disk, keyed by slug.
     *
     * @return array<string, array>
     */
    public static function discover(): array
    {
        $dir = self::viewDir();
        if (!is_dir($dir)) {
            return [];
        }

        $found = [];
        foreach (File::files($dir) as $file) {
            $name = $file->getFilename();
            if (!str_ends_with($name, '.blade.php') || str_starts_with($name, '_')) {
                continue;
            }

            $slug = substr($name, 0, -strlen('.blade.php'));
            if (!self::isValidSlug($slug)) {
                continue;
            }

            $found[$slug] = self::parseHeader((string) file_get_contents($file->getPathname()), $slug);
        }

        ksort($found);

        return $found;
    }

    /**
     * Register every design file that is not in the table yet. Existing rows
     * keep the name/description an admin may have edited; only brand-new
     * files are inserted. Returns ['added' => [...slugs], 'missing' => [...slugs]].
     */
    public static function sync(?int $userId = null): array
    {
        $files = self::discover();
        $existing = CustomReportTemplate::pluck('slug')->all();

        $added = [];
        foreach ($files as $slug => $meta) {
            if (in_array($slug, $existing, true)) {
                continue;
            }

            CustomReportTemplate::create([
                'slug' => $slug,
                'name' => $meta['name'],
                'level' => $meta['level'],
                'description' => $meta['description'],
                'is_active' => true,
                'created_by' => $userId,
            ]);
            $added[] = $slug;
        }

        return [
            'added' => $added,
            'missing' => array_values(array_diff($existing, array_keys($files))),
        ];
    }

    // ─── Assignment lookup (used by every print route) ──────────────────────

    /**
     * The ACTIVE assignment (with its template) for a school + level, or null.
     * Defensive on purpose: if the migration has not been run yet, the
     * template file is gone or the template is switched off, the school
     * simply keeps its normal built-in designs instead of erroring.
     */
    public static function assignment($schoolId, string $level): ?object
    {
        if (empty($schoolId) || !isset(self::LEVELS[$level])) {
            return null;
        }

        $memo = $schoolId . '|' . $level;
        if (array_key_exists($memo, self::$assignmentCache)) {
            return self::$assignmentCache[$memo];
        }

        try {
            $row = SchoolCustomReportCard::with('template')
                ->where('school_id', $schoolId)
                ->where('level', $level)
                ->where('is_active', true)
                ->first();
        } catch (Throwable $e) {
            return self::$assignmentCache[$memo] = null;
        }

        $tpl = $row?->template;
        if (!$row || !$tpl || !$tpl->is_active) {
            return self::$assignmentCache[$memo] = null;
        }

        if (!is_file(self::filePath($tpl->slug)) || !view()->exists(self::viewName($tpl->slug))) {
            return self::$assignmentCache[$memo] = null;
        }

        return self::$assignmentCache[$memo] = (object) [
            'row' => $row,
            'template' => $tpl,
            'lock_to_custom' => (bool) $row->lock_to_custom,
        ];
    }

    /**
     * Decide whether a custom design applies, and if so which.
     *
     * $requested is the ?template= value in play (explicit query param, or
     * the template merged in from the class's saved settings). For a LOCKED
     * assignment it is ignored completely - the school only ever sees its
     * own design. For an unlocked one the custom design is merely the
     * default: a school that explicitly picked a built-in design keeps it.
     *
     * @return array{view:string,key:string,slug:string,template:CustomReportTemplate,meta:array,level:string,locked:bool}|null
     */
    public static function resolve($schoolId, string $level, ?string $requested = null): ?array
    {
        $a = self::assignment($schoolId, $level);
        if (!$a) {
            return null;
        }

        $asksBuiltin = !empty($requested) && !self::isCustomTemplateKey($requested);
        if ($asksBuiltin && !$a->lock_to_custom) {
            return null;
        }

        $slug = $a->template->slug;

        return [
            'view' => self::viewName($slug),
            'key' => self::templateKey($slug),
            'slug' => $slug,
            'template' => $a->template,
            'meta' => self::readMeta($slug) ?? [],
            'level' => $level,
            'locked' => $a->lock_to_custom,
        ];
    }

    /**
     * Same as resolve() but works out the level from a class id.
     */
    public static function resolveForClass($schoolId, $classId, ?string $requested = null): ?array
    {
        return self::resolve($schoolId, self::levelForClass($classId), $requested);
    }

    /**
     * Every level this school has an active custom design for - powers the
     * pass-slips index card and the customise-page redirect.
     *
     * @return array<string, array>
     */
    public static function activeForSchool($schoolId): array
    {
        $out = [];
        foreach (array_keys(self::LEVELS) as $level) {
            if ($resolved = self::resolve($schoolId, $level)) {
                $out[$level] = $resolved;
            }
        }

        return $out;
    }

    /**
     * Toggle registry entries a design declared, grouped like
     * Helper::passslipTogglesForTemplate() so the customise page can reuse
     * the same markup.
     */
    public static function toggleGroups(array $meta): array
    {
        $registry = Helper::passslipToggleRegistry();
        $grouped = [];

        foreach ($meta['toggles'] ?? [] as $key) {
            if (isset($registry[$key])) {
                $grouped[$registry[$key]['group']][$key] = $registry[$key];
            }
        }

        return $grouped;
    }

    /** True when the custom-report tables exist (safe to call before migrating). */
    public static function tablesReady(): bool
    {
        try {
            return Schema::hasTable('custom_report_templates') && Schema::hasTable('school_custom_report_cards');
        } catch (Throwable $e) {
            return false;
        }
    }
}
