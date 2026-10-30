<?php

namespace App\Services;

use App\Models\DivisionBand;
use App\Models\GradingScale;
use App\Models\GradingScheme;
use App\Models\School;
use Illuminate\Support\Facades\DB;

/**
 * Grading schemes are per-school. There is no global/system scheme anymore —
 * every school gets its own starter set of schemes the moment it is created,
 * and from that point on the school fully owns them: edit, activate/deactivate,
 * delete, or add brand new ones, with no "duplicate from a template" step.
 *
 * This class is the single source of truth for what a *new* school starts
 * with. It's used by:
 *   - App\Http\Controllers\SchoolController::createNewSchool() (new schools)
 *   - Database\Seeders\GradingSchemeSeeder (fresh installs / dev seeding)
 */
class GradingSchemeDefaults
{
    /** master_datas.md_id of the "Secondary" school product. */
    public const SECONDARY_PRODUCT_MD_ID = 339;

    /**
     * master_datas.md_id of the Primary school products:
     * 219 = Primary Theology, 231 = Primary Secular,
     * 289 = Both Primary Theology and Secular.
     */
    public const PRIMARY_PRODUCT_MD_IDS = [219, 231, 289];

    /** Default key of the A-Level (UACE) scheme. */
    public const UACE_KEY = 'uace';

    /** @var array<int,bool> */
    private static array $aLevelCache = [];

    /**
     * Senior 5 / Senior 6 — detected from the class itself (master data
     * "Secondary A-Level Classes", with a name fallback), never from the
     * stream, so every stream of the class is covered.
     */
    public static function isALevelClass($classId): bool
    {
        $classId = (int) $classId;

        if ($classId <= 0) {
            return false;
        }

        if (isset(self::$aLevelCache[$classId])) {
            return self::$aLevelCache[$classId];
        }

        $row = DB::table('master_datas')->where('md_id', $classId)->first(['md_master_code_id', 'md_name']);

        $isA = $row && (
            (int) $row->md_master_code_id === (int) config('constants.options.SECONDARY_ALEVEL_CLASSES')
            || preg_match('/^\s*(senior|s)\s*[.\-]?\s*[56]\s*$/i', (string) $row->md_name) === 1
        );

        return self::$aLevelCache[$classId] = (bool) $isA;
    }

    /** The school's active A-Level (UACE) scheme, if it has one. */
    public static function uaceSchemeFor($schoolId): ?GradingScheme
    {
        return GradingScheme::availableTo($schoolId)->where('default_key', self::UACE_KEY)->first();
    }

    /**
     * Restore the built-in defaults that apply to this school's product(s).
     *  - missing defaults are re-created;
     *  - existing ones (matched by default_key, else by original name) are
     *    reset to the factory bands when $resetExisting is true.
     * Returns ['created' => n, 'reset' => n].
     *
     * @param array<int,string>|null $onlyKeys limit to these default keys
     */
    public static function restoreForSchool(int $schoolId, ?int $createdBy = null, ?array $onlyKeys = null, bool $resetExisting = true): array
    {
        $school = School::find($schoolId);
        $productIds = $school ? $school->productMdIds() : [];
        $result = ['created' => 0, 'reset' => 0];

        DB::transaction(function () use ($schoolId, $createdBy, $onlyKeys, $resetExisting, $productIds, &$result) {
            foreach (self::definitions() as $def) {
                if ($onlyKeys !== null && !in_array($def['key'], $onlyKeys, true)) {
                    continue;
                }

                if (!self::appliesToProducts($def, $productIds)) {
                    continue;
                }

                $scheme = GradingScheme::where('school_id', $schoolId)->where('default_key', $def['key'])->first()
                    ?? GradingScheme::where('school_id', $schoolId)->where('name', $def['name'])->first();

                if ($scheme) {
                    if (!$resetExisting) {
                        if (!$scheme->default_key) {
                            $scheme->update(['default_key' => $def['key']]);
                        }
                        continue;
                    }

                    $scheme->update([
                        'default_key' => $def['key'],
                        'name' => $def['name'],
                        'description' => $def['description'],
                        'total_marks' => $def['total_marks'],
                        'pass_mark' => $def['pass_mark'],
                        'is_active' => true,
                        'ungraded_on_fail' => $def['ungraded_on_fail'] ?? true,
                    ]);
                    $scheme->bands()->delete();
                    $scheme->divisionBands()->delete();
                    self::fillBands($scheme, $def);
                    $result['reset']++;
                    continue;
                }

                $scheme = GradingScheme::create([
                    'school_id' => $schoolId,
                    'default_key' => $def['key'],
                    'name' => $def['name'],
                    'description' => $def['description'],
                    'total_marks' => $def['total_marks'],
                    'pass_mark' => $def['pass_mark'],
                    // Never steal the school's current default.
                    'is_default' => $def['is_default'] && !GradingScheme::where('school_id', $schoolId)->where('is_default', true)->exists(),
                    'is_active' => true,
                    'ungraded_on_fail' => $def['ungraded_on_fail'] ?? true,
                    'created_by' => $createdBy,
                ]);
                self::fillBands($scheme, $def);
                $result['created']++;
            }
        });

        return $result;
    }

    /**
     * Does this default belong to a school enrolled in $productIds?
     *  - Secondary schools get UCE + UACE.
     *  - Primary schools get Junior School + Simple A-F.
     *  - A school on any other product (e.g. Idaad And Thanawi) keeps the
     *    previous behaviour: every default except the Secondary-only UACE.
     */
    public static function appliesToProducts(array $definition, array $productIds): bool
    {
        $products = $definition['products'] ?? null;

        if ($products === null) {
            return true;
        }

        if (!empty(array_intersect($products, $productIds))) {
            return true;
        }

        $known = array_merge([self::SECONDARY_PRODUCT_MD_ID], self::PRIMARY_PRODUCT_MD_IDS);
        $isOtherProduct = empty(array_intersect($known, $productIds));

        return $isOtherProduct && $definition['key'] !== self::UACE_KEY;
    }

    private static function fillBands(GradingScheme $scheme, array $definition): void
    {
        foreach ($definition['bands'] as $i => $band) {
            GradingScale::create([
                'grading_scheme_id' => $scheme->id,
                'grade'      => $band[0],
                'min_mark'   => $band[1],
                'max_mark'   => $band[2],
                'remark'     => $band[3],
                'points'     => $band[4],
                'sort_order' => $i,
            ]);
        }

        foreach (($definition['division_bands'] ?? []) as $i => $band) {
            DivisionBand::create([
                'grading_scheme_id' => $scheme->id,
                'min_aggregate' => $band[0],
                'max_aggregate' => $band[1],
                'division'      => $band[2],
                'remark'        => $band[3],
                'sort_order'    => $i,
            ]);
        }
    }

    /**
     * Create this school's starter grading schemes (+ bands), but only if the
     * school doesn't already have any schemes of its own. Safe to call more
     * than once for the same school.
     */
    public static function seedForSchool(int $schoolId, ?int $createdBy = null): void
    {
        if (GradingScheme::where('school_id', $schoolId)->exists()) {
            return;
        }

        self::restoreForSchool($schoolId, $createdBy, null, false);
    }

    /**
     * Each band is [grade, min_mark%, max_mark%, remark, points].
     * Percentages, so they apply regardless of the scheme's total_marks.
     */
    public static function definitions(): array
    {
        // UCE (O-Level, Senior 1-4) is graded exactly like UACE: A-E with
        // achievement-level remarks, no D1-F9 and no Division bands.
        $aToE = [
            ['A', 80, 100,   'Exceptional',  5],
            ['B', 60, 79.99, 'Outstanding',  4],
            ['C', 40, 59.99, 'Satisfactory', 3],
            ['D', 20, 39.99, 'Basic',        2],
            ['E', 0,  19.99, 'Elementary',   1],
        ];

        return [
            [
                // Secondary — O-Level (Senior 1-4).
                'key'         => 'uce',
                'products'    => [self::SECONDARY_PRODUCT_MD_ID],
                'name'        => 'UCE Standard (New Curriculum)',
                'description' => 'O-Level (Senior 1-4): grades A-E with achievement levels, out of 100 marks.',
                'total_marks' => 100,
                'pass_mark'   => 40,
                'is_default'  => true,
                'ungraded_on_fail' => true,
                'bands' => $aToE,
            ],
            [
                // Secondary — A-Level (Senior 5-6). Applied automatically to those
                // classes when an exam has no scheme chosen for them.
                'key'         => 'uace',
                'products'    => [self::SECONDARY_PRODUCT_MD_ID],
                'name'        => 'UACE (A-Level) Standard',
                'description' => 'A-Level (Senior 5-6): grades A-E with achievement levels, out of 100 marks.',
                'total_marks' => 100,
                'pass_mark'   => 40,
                'is_default'  => false,
                'bands' => $aToE,
            ],
            [
                // Primary.
                'key'         => 'junior',
                'products'    => self::PRIMARY_PRODUCT_MD_IDS,
                'name'        => 'Junior School (80 Marks)',
                'description' => 'Lower/junior classes, out of 80 marks, pass at 40 marks (50%).',
                'total_marks' => 80,
                'pass_mark'   => 40,
                'is_default'  => false,
                'bands' => [
                    ['A',  80, 100, 'Excellent', 5],
                    ['B',  70, 79,  'Very Good', 4],
                    ['C',  60, 69,  'Good', 3],
                    ['D',  50, 59,  'Fair', 2],
                    ['E',  0,  49,  'Fail', 1],
                ],
            ],
            [
                // Primary.
                'key'         => 'simple_af',
                'products'    => self::PRIMARY_PRODUCT_MD_IDS,
                'name'        => 'Simple A-F (100 Marks)',
                'description' => 'Common single-letter grading, out of 100 marks, pass at 50%.',
                'total_marks' => 100,
                'pass_mark'   => 50,
                'is_default'  => true,
                'bands' => [
                    ['A', 80, 100, 'Excellent', 5],
                    ['B', 70, 79,  'Very Good', 4],
                    ['C', 60, 69,  'Good', 3],
                    ['D', 50, 59,  'Pass', 2],
                    ['F', 0,  49,  'Fail', 0],
                ],
            ],
        ];
    }
}
