<?php

namespace App\Services;

use App\Helpers\PermissionHelper;
use App\Models\ModuleFeature;
use App\Models\School;
use App\Models\SchoolRole;
use App\Models\SystemModule;
use Illuminate\Support\Facades\DB;

/**
 * Starter roles every new school gets, so nobody builds roles from scratch:
 *
 *   HeadTeacher  every school right the school's product(s) can use.
 *   Teacher      only what a teacher must do: marks entry and, for Secondary
 *                schools, NLSC / open assessments, plus own timetable and
 *                notifications.
 *
 * Both are ordinary SchoolRole rows — admins edit, extend or delete them in
 * User Rights & Permissions like any other role.
 *
 * Product aware: a Primary-only school never gets the Secondary-only
 * features (NLSC, assessments, O/A-Level setup, O-Level report cards), and a
 * Secondary-only school never gets the Early Years assessment-scale features.
 * Existing roles are never modified; only missing ones are created.
 */
class DefaultRolesService
{
    public const HEAD_TEACHER = 'HeadTeacher';
    public const TEACHER = 'Teacher';

    /** Mirrors GradingSchemeDefaults::SECONDARY_PRODUCT_MD_ID. */
    private const SECONDARY_PRODUCT_MD_ID = 339;

    /** Features that only make sense for the Secondary product. */
    private const SECONDARY_ONLY = [
        'view_alevel_combinations', 'save_alevel_combinations', 'manage_alevel_subjects',
        'view_olevel_electives', 'save_olevel_electives', 'manage_olevel_subjects',
        'view_nlsc_projects', 'add_nlsc_project', 'edit_nlsc_project', 'delete_nlsc_project',
        'view_nlsc_topics', 'add_nlsc_topic', 'edit_nlsc_topic', 'delete_nlsc_topic',
        'view_nlsc_achievements', 'add_nlsc_achievement', 'edit_nlsc_achievement', 'delete_nlsc_achievement',
        'view_nlsc_assessments', 'manage_nlsc_assessments', 'delete_nlsc_assessments', 'enter_nlsc_marks',
        'view_open_assessments', 'manage_open_assessments', 'delete_open_assessments',
        'enter_open_assessment_marks', 'view_open_assessments_overview',
        'view_assessment_expectations', 'manage_assessment_expectations',
        'view_olevel_report_cards', 'create_olevel_report_card', 'edit_olevel_report_card', 'delete_olevel_report_card',
    ];

    /** Features for Early Years / Primary grading only (not used by pure Secondary schools). */
    private const NON_SECONDARY_ONLY = [
        'view_assessment_scales', 'manage_assessment_scales', 'assign_assessment_scales',
    ];

    /** The Teacher role's feature list (anything not applicable to the product is dropped). */
    private const TEACHER_FEATURES = [
        'view_dashboard', 'view_dashboard_school_info',
        'view_exams', 'view_marks_entry', 'enter_marks',
        'view_remarks', 'enter_remarks',
        'view_nlsc_assessments', 'manage_nlsc_assessments', 'enter_nlsc_marks',
        'view_open_assessments', 'manage_open_assessments', 'enter_open_assessment_marks',
        'view_teacher_schedule',
        'view_my_notifications', 'mark_notification_read', 'mark_all_read', 'push_notifications',
    ];

    /**
     * Create whichever of the two default roles the school doesn't have yet.
     * Roles that already exist (by name, case-insensitive) are left untouched.
     *
     * @return array<string,bool> role name => created now?
     */
    public static function seedForSchool(int $schoolId): array
    {
        $school = School::find($schoolId);

        if (!$school) {
            return [];
        }

        $productIds = $school->productMdIds();
        $hasSecondary = in_array(self::SECONDARY_PRODUCT_MD_ID, $productIds, true);
        // No product recorded at all: don't hide anything.
        $hasNonSecondary = empty($productIds) || count(array_diff($productIds, [self::SECONDARY_PRODUCT_MD_ID])) > 0;

        $excluded = array_merge(
            $hasSecondary ? [] : self::SECONDARY_ONLY,
            $hasNonSecondary ? [] : self::NON_SECONDARY_ONLY
        );

        $features = ModuleFeature::with('module:id,key,is_active')->get()
            ->filter(fn ($f) => $f->module && $f->module->is_active && !in_array($f->key, $excluded, true));

        $definitions = [
            self::HEAD_TEACHER => [
                'description' => 'Full access to everything available to this school. Edit in User Rights & Permissions.',
                'feature_keys' => $features->pluck('key')->all(),
            ],
            self::TEACHER => [
                'description' => 'Marks entry' . ($hasSecondary ? ', assessments' : '') . ' and own schedule. Admins can add more rights.',
                'feature_keys' => array_values(array_intersect(self::TEACHER_FEATURES, $features->pluck('key')->all())),
            ],
        ];

        $created = [];

        DB::transaction(function () use ($schoolId, $definitions, $features, &$created) {
            $allModules = SystemModule::where('is_active', true)->pluck('id');
            $now = now();

            foreach ($definitions as $name => $def) {
                $exists = SchoolRole::where('school_id', $schoolId)
                    ->whereRaw('LOWER(name) = ?', [strtolower($name)])
                    ->exists();

                if ($exists) {
                    $created[$name] = false;
                    continue;
                }

                $role = SchoolRole::create([
                    'school_id' => $schoolId,
                    'name' => $name,
                    'description' => $def['description'],
                    'is_active' => true,
                ]);

                $allowed = $features->whereIn('key', $def['feature_keys']);
                $allowedFeatureIds = $allowed->pluck('id')->all();
                $allowedModuleIds = $allowed->pluck('module_id')->unique()->all();

                // Same shape PermissionHelper / saveRolePermissions() expect:
                // one row per module and per feature, can_access true/false.
                $moduleRows = $allModules->map(fn ($id) => [
                    'school_role_id' => $role->id, 'module_id' => $id,
                    'can_access' => in_array($id, $allowedModuleIds, true),
                    'created_at' => $now, 'updated_at' => $now,
                ])->all();

                $featureRows = ModuleFeature::pluck('id')->map(fn ($id) => [
                    'school_role_id' => $role->id, 'feature_id' => $id,
                    'can_access' => in_array($id, $allowedFeatureIds, true),
                    'created_at' => $now, 'updated_at' => $now,
                ])->all();

                foreach (array_chunk($moduleRows, 200) as $chunk) {
                    DB::table('role_module_access')->insert($chunk);
                }
                foreach (array_chunk($featureRows, 200) as $chunk) {
                    DB::table('role_feature_access')->insert($chunk);
                }

                $created[$name] = true;
            }
        });

        PermissionHelper::flushSchoolRolesCache($schoolId);

        return $created;
    }
}
