<?php

/**
 * Admin Portal permission catalogue — the single source of truth.
 *
 * Every capability of the Admin Portal is one "module.action" key listed here.
 * Roles (admin_roles) store these keys; routes are protected with
 *   ->middleware('admin.can:schools.edit')        (a specific capability)
 *   ->middleware('admin.module:master_data')      (any capability of a module)
 * and controllers / Blade use App\Support\AdminAccess::can('schools.edit').
 *
 * Super administrators (users.is_super_admin) implicitly hold every key,
 * including keys added here later. To add a feature: add the key below, guard
 * the route/controller/menu item with it, done — it shows up in the matrix.
 *
 * Note: the admin Dashboard is the landing page of every admin login and is
 * deliberately not permission-gated (same as the school portal).
 */
return [

    'modules' => [

        'schools' => [
            'name' => 'Schools',
            'icon' => 'fa fa-school',
            'description' => 'Register and manage the schools on the platform',
            'permissions' => [
                'view' => 'View schools & profiles',
                'create' => 'Register a new school',
                'edit' => 'Edit school details, profile & options',
                'change_status' => 'Activate / suspend a school',
                'delete' => 'Delete a school',
                'toggle_custom_subjects' => 'Lock / unlock custom subjects for a school',
                'enter' => 'Select / switch into a school',
            ],
        ],

        'academic_calendar' => [
            'name' => 'Academic Years & Terms',
            'icon' => 'fa fa-calendar-alt',
            'description' => 'Academic years and term dates',
            'permissions' => [
                'view' => 'View academic years & term dates',
                'manage' => 'Create, edit, (de)activate and delete years & terms',
            ],
        ],

        'school_rights' => [
            'name' => 'School Roles & Staff',
            'icon' => 'fa fa-sitemap',
            'description' => 'Fix roles, permissions and staff for any school (Schools & Roles screen)',
            'permissions' => [
                'view' => 'View every school\'s roles & staff',
                'manage_roles' => 'Create, edit, delete school roles and their permissions',
                'assign_roles' => 'Assign / change a school staff member\'s role',
                'create_staff' => 'Create the founding staff account of a school',
                'toggle_staff_cap' => 'Open / close staff creation for a school',
            ],
        ],

        'master_data' => [
            'name' => 'Master Data & Codes',
            'icon' => 'fa fa-database',
            'description' => 'System master data and master codes',
            'permissions' => [
                'view_data' => 'View master data',
                'create_data' => 'Create master data',
                'edit_data' => 'Edit master data',
                'delete_data' => 'Delete master data',
                'view_codes' => 'View master codes',
                'create_codes' => 'Create master codes',
                'edit_codes' => 'Edit master codes',
                'delete_codes' => 'Delete master codes',
            ],
        ],

        'secondary_subjects' => [
            'name' => 'Secondary Subject Lists',
            'icon' => 'fa fa-graduation-cap',
            'description' => 'Global A-Level and O-Level subject lists',
            'permissions' => [
                'view' => 'View A/O-Level subject lists',
                'add' => 'Add a subject',
                'edit' => 'Edit a subject',
                'delete' => 'Delete a subject',
            ],
        ],

        'nlsc_curriculum' => [
            'name' => 'NLSC Curriculum (Master)',
            'icon' => 'fa fa-book-open',
            'description' => 'NLSC topics, competency areas, projects and subject achievements',
            'permissions' => [
                'view' => 'View NLSC master curriculum',
                'add' => 'Add NLSC master records',
                'edit' => 'Edit NLSC master records',
                'import' => 'Bulk import NLSC master records',
                'delete' => 'Delete NLSC master records',
            ],
        ],

        'billing' => [
            'name' => 'Billing',
            'icon' => 'fa fa-file-invoice-dollar',
            'description' => 'SaaS billing: termly invoices, payments and pricing for schools',
            'permissions' => [
                'view' => 'View billing dashboard, invoices, payments and receipts',
                'manage_terms' => 'Create, edit, close and set the current billing term',
                'generate_invoices' => 'Generate and issue invoices for a term',
                'edit_invoices' => 'Edit invoices (rate, discount, adjustments, recount, cancel, write off)',
                'record_payments' => 'Record and reverse school payments',
                'manage_pricing' => 'Set per-school rates, discounts and exemptions',
                'view_reports' => 'View billing reports and export CSV',
                'manage_settings' => 'Change billing settings (default rate, tax, invoice details)',
            ],
        ],

        'custom_report_cards' => [
            'name' => 'Custom Report Cards',
            'icon' => 'fa fa-chart-bar',
            'description' => 'Per-school custom report card designs',
            'permissions' => [
                'view' => 'View designs',
                'sync' => 'Register designs found on disk',
                'manage_templates' => 'Rename, enable/disable and delete designs',
                'manage_assignments' => 'Assign designs to schools',
                'use_studio' => 'Use the preview studio',
            ],
        ],

        'notifications' => [
            'name' => 'Notifications',
            'icon' => 'fa fa-bell',
            'description' => 'Broadcast notifications to schools and staff',
            'permissions' => [
                'view' => 'View sent notifications',
                'create' => 'Open the create-notification form',
                'send' => 'Broadcast a notification',
                'delete' => 'Delete a notification',
            ],
        ],

        'admin_rights' => [
            'name' => 'Admin Rights & Privileges',
            'icon' => 'fa fa-user-shield',
            'description' => 'Admin roles, permissions and admin user accounts',
            'permissions' => [
                'view_overview' => 'View the rights overview',
                'view_roles' => 'View admin roles',
                'create_role' => 'Create an admin role',
                'edit_role' => 'Edit an admin role',
                'delete_role' => 'Delete an admin role',
                'view_permissions' => 'View the permission matrix',
                'assign_permissions' => 'Change a role\'s permissions (only ones you hold yourself)',
                'view_users' => 'View admin users',
                'create_user' => 'Create an admin user',
                'edit_user' => 'Edit an admin user',
                'assign_role' => 'Assign a role to an admin user (only roles within your own rights)',
                'toggle_status' => 'Activate / deactivate an admin user',
                'reset_password' => 'Reset an admin user\'s password',
                'delete_user' => 'Delete an admin user',
                'view_audit' => 'View the rights audit log',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Legacy bridge (PermissionHelper::canFeature / canModule from the Admin Portal)
    |--------------------------------------------------------------------------
    | Shared code (e.g. NotificationController) still asks PermissionHelper
    | using school-style keys. When the caller is an admin-portal session these
    | are translated to the permission keys above. Anything not listed here is
    | denied to non-super admins (secure by default).
    */
    'legacy_feature_map' => [
        'view_notifications' => 'notifications.view',
        'create_notification' => 'notifications.create',
        'send_notification' => 'notifications.send',
        'delete_notification' => 'notifications.delete',
    ],

    // Personal, harmless features every active admin keeps (own inbox, push opt-in).
    'legacy_open_features' => [
        'view_my_notifications',
        'mark_notification_read',
        'mark_all_read',
        'push_notifications',
    ],

    'legacy_module_map' => [
        'notifications' => 'notifications',
    ],
];
