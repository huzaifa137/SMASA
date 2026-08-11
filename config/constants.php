<?php

return [
    'options' => [
        'SCHOOL_PRODUCTS' => 1,
        'SCHOOL_GENDER' => 2,
        'SCHOOL_OWNERSHIP' => 3,
        'REGIONAL_LEVEL' => 4,
        'SCHOOL_POPULATION' => 5,
        'SCHOOL_TYPE' => 6,
        'SCHOOL_TERMS' => 7,
        // 'SCHOOL_OPTIONALS'  => ,

        // SUBJECTS

        'TECHNICAL_SUBJECTS' => 8,

        'IDAAD_ARABIC_LANGUAGE' => 23,
        'IDAAD_FAITH_AND_CIVILIZATION' => 24,
        'IDAAD_JURISPRUDENCE_AND_ITS_SOURCES' => 25,
        'IDAAD_PROPHETIC_TRADITIONS' => 26,
        'IDAAD_QURAN_ITS_SCIENCES' => 27,


        'THANAWI_ARABIC_LANGUAGE' => 28,
        'THANAWI_FAITH_AND_CIVILIZATION' => 29,
        'THANAWI_JURISPRUDENCE_AND_ITS_SOURCES' => 30,
        'THANAWI_PROPHETIC_TRADITIONS' => 31,
        'THANAWI_QURAN_ITS_SCIENCES' => 32,

        // SUBJECTS PRIMARY SECULAR

        'NURSERY_BABY_CLASS' => 35,
        'NURSERY_MIDDLE_CLASS' => 36,
        'NURSERY_TOP_CLASS' => 37,
        'LOWER_PRIMARY_P1' => 38,
        'LOWER_PRIMARY_P2' => 39,
        'LOWER_PRIMARY_P3' => 40,
        'UPPER_PRIMARY_P4_P7' => 41,
        // CLASSES

        'O_LEVEL' => 16,
        'A_LEVEL' => 17,
        'PRIMARY_LEVEL' => 18,
        'URPF' => 43,
        'PRIMARY_THEOLOGY' => 33,
        'PRIMARY_THEOLOGY_CLASSES' => 34,
        'PRIMARY_SECULAR_CLASSES' => 42,

        // SYSTEM SECTIONS

        // 1.SCHOOOL

        'School' => 16,
        'ThanawiPapers' => 20,
        'IdaadPapers' => 21,
        'ExaminationName' => 22,

    ],

    /*
    |--------------------------------------------------------------------------
    | Early Years Grading (Nursery / Kindergarten / Pre-Primary)
    |--------------------------------------------------------------------------
    | These classes (Baby Class, Middle Class, Top Class in the current
    | seed data) do not sit numeric exams. Instead, per subject, a teacher
    | picks one of the 3 system comments below (or writes their own) and
    | the student is scored 1-3 on that subject instead of out of the
    | exam's normal total_marks.
    |
    | master_codes: 35 = NURSERY_BABY_CLASS, 36 = NURSERY_MIDDLE_CLASS,
    | 37 = NURSERY_TOP_CLASS — any subject whose master_datas row hangs
    | off one of these codes is treated as an early-years subject.
    */
    'early_years' => [
        'master_codes' => [35, 36, 37],
        'max_mark' => 3,
        'presets' => [
            ['marks' => 1, 'label' => "Works under Teacher's Guidance", 'remark' => 'Fair'],
            ['marks' => 2, 'label' => 'Works with Minimum Supervision', 'remark' => 'Good'],
            ['marks' => 3, 'label' => 'Works Independently', 'remark' => 'Excellent'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | School Products <-> Class Types
    |--------------------------------------------------------------------------
    | A "School Product" (master code SCHOOL_PRODUCTS) is what a school is
    | sold/enrolled under. Two of the four existing products are already
    | fixed bundles of more than one underlying class type ("Idaad And
    | Thanawi" = O-Level + A-Level, "Both Primary Theology and Secular" =
    | Primary Theology + Primary Secular). The School Products merge
    | feature (see App\Services\SchoolProductMergeService) generalises
    | this: a school can now hold ANY combination of the products below
    | at once, and every class type that any of its products maps to is
    | unioned together wherever classes/subjects are listed.
    |
    | Keys here MUST exactly match the md_name of a SCHOOL_PRODUCTS
    | master_datas row - this mirrors how the rest of the codebase
    | already compares products (Helper::recordMdname() against these
    | same literal strings in ClassandSubjectController/StudentController).
    */
    'product_class_types' => [
        'Idaad And Thanawi' => ['O-Level', 'A-Level'],
        'Primary Theology' => ['Primary Theology'],
        'Primary Secular' => ['Primary Secular'],
        'Both Primary Theology and Secular' => ['Primary Theology', 'Primary Secular'],
    ],

    // Which config('constants.options.*') master-code holds the class list
    // for each class type.
    'class_type_master_codes' => [
        'O-Level' => 'O_LEVEL',
        'A-Level' => 'A_LEVEL',
        'Primary Theology' => 'PRIMARY_THEOLOGY_CLASSES',
        'Primary Secular' => 'PRIMARY_SECULAR_CLASSES',
    ],

    // The custom_subjects.class_type / class_subjects.subject_type value
    // each class type is stored under (matches CustomSubjectController::CLASS_TYPES).
    'class_type_subject_types' => [
        'O-Level' => 'idaad',
        'A-Level' => 'thanawi',
        'Primary Theology' => 'primary_theology',
        'Primary Secular' => 'primary_secular',
    ],
];


// Access specific features (Each of these Add,Delete,Edit,View) ====> Accessing specific user right 
// public static function userHasSpecificPermission($userId, $permissionFeature, $permissionName, $permissionScope)

// Have all Rights for that feauture (All of these Add,Delete,Edit,View) ====> Accessing all user crud
// public static function userHasAllPermissions($userId, $permissionName, $permissionScope)

// Access specific Section in the system (All of these Add,Delete,Edit,View) ====> Accessing all user crud
// public static function userPermissionSectionAccess($userId, $permissionName, $permissionScope)


// if (PermissionHelper::userPermissionSectionAccess(session('LoggedStudent'), 155, 'school')) {

// } else {
//     return redirect()->route('student.dashboard')->with('error', 'You do not have permission to access that feature!');
// }

// @if (PermissionHelper::userPermissionSectionAccess(session('LoggedStudent'), 155, 'school'))
// @else

// @endif

// @if (PermissionHelper::userHasSpecificPermission(session('LoggedStudent'), 'view_155', 155, 'school'))
// @else
// <p style="color: red">Access restricted</p>
// @endif


// Most use ones Logic Implementation
// <<<< For Section and Routes>>>>
// =====> Used on Routes and applied on controllers also to limit the functionality access from that side also
// (Blade and Routes format Implementation)
//  @if (PermissionHelper::userHasSpecificPermission(session('LoggedAdmin'), Helper::getPermissionCode('view', config('constants.module_names')[2]), config('constants.options.Land'), 'school'))
// // (Controller format Implementation)
// Helper::checkPermissionOrAbort(Helper::getPermissionCode('view', config('constants.module_names')[2]), config('constants.options.Land'));

// <<<< For Custom Features Accessibility >>>>
//  @if (PermissionHelper::userHasFeature(session('LoggedAdmin'), config('constants.options.addNewPlots')))

// @if (PermissionHelper::userHasFeature(session('LoggedAdmin'), config('constants.options.addNewPlots')))
//     <li class="nav-item">
//         <a class="nav-link" href="javascript:void();">
//             <i class="mdi mdi-domain ml-1"></i>
//             <span style="padding-left: 2px;">Add New Estate Test</span>
//         </a>
//     </li>
// @endif


// Implementation Easily ===<>=== 


// 1. (VIEW CLOSING ALL)
// 2. (IMPLEMENTATION OF ONE LAYOUT IF NOT EDIT, DELETE, ETC)
// -----------------------------------------------------------------------------------------
// Helper::checkPermissionOrAbort(Helper::getPermissionCode('view', config('constants.module_names')[2]), config('constants.options.Land'));
// Helper::checkCustomPermissionOrAbort(config('constants.options.sellPlots'));
// ----> ADD,EDIT,DELETE,ETC 
// @if (PermissionHelper::userHasSpecificPermission(session('LoggedAdmin'), Helper::getPermissionCode('add', config('constants.module_names')[2]), config('constants.options.Land'), 'school'))