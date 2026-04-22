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
        'URPF' => 19,
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
