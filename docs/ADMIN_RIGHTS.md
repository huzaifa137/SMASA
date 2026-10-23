# Admin Rights & Privileges

Roles and permissions for the **Admin Portal** (the school portal keeps its own
system under *User Rights*).

## Concepts

| Thing | What it is |
|---|---|
| **Super Administrator** | A user with `users.is_super_admin = 1`. Holds *every* permission, including ones added later. Not a role. |
| **Admin role** | A named set of permission keys (`admin_roles` + `admin_role_permissions`). |
| **Admin user** | A `users` row with `user_role = 'admin'`. Has either the super-admin flag, or one role, or nothing (dashboard only). |
| **Permission** | A `module.action` key such as `schools.edit`. Catalogue: `config/admin_permissions.php`. |

The Dashboard is the landing page of every admin login and is intentionally not gated.

## First-time setup / deployment

```bash
php artisan migrate
php artisan db:seed --class=SystemModulesSeeder   # school catalogue (master_data module no longer present)
php artisan admin:create-super-admin              # only needed on a fresh install
php artisan db:seed --class=AdminRolesSeeder      # OPTIONAL starter roles
php artisan config:clear && php artisan route:clear && php artisan view:clear
```

The migration promotes every user attached to the legacy **Administrators**
system role to Super Administrator, so existing admins keep full access. Legacy
**Sales Representative** admins receive a limited "Sales Representative" admin
role (view/create/edit schools + academic calendar). If a system has admins but
no super admin after backfill, the earliest admin is promoted so it can never be
headless — review this under *Admin Rights → Admin Users*.

## Super administrator rules

* More than one is allowed. Only a super admin can grant or remove the status.
* The **last active** super admin can never be demoted, deactivated or deleted.
* Nobody can deactivate, delete or change the role of their own account.
* Super admins are managed by super admins only.

## No privilege escalation

A non-super admin can only: grant/remove permissions **they hold themselves**
(everything above their level stays untouched), assign roles whose permissions
are a subset of theirs, and edit/delete such roles. They cannot touch an admin who
holds more than they do.

## Protecting something new

1. Add the key to `config/admin_permissions.php` (it appears in the matrix automatically).
2. Guard it:
   * route: `->middleware('admin.can:module.action')` (any-of: `admin.can:a.b,c.d`), or `admin.module:module`;
   * screens shared with the school portal: `admin.guard:module.action` (constrains admin sessions only);
   * controller: `AdminAccess::denyUnless('module.action')`;
   * Blade: `@if(\App\Support\AdminAccess::can('module.action'))`.

Nothing is cached across requests: revoking a right or deactivating an admin
takes effect on their next request (`AdminAuth` also ends the session of a
deactivated/deleted admin).

## Removed

* The old static system: `/user-rights-and-previledges/*` routes, `UserRightsAndPreviledges`
  controller, `UserRights/*` views, `Permission`/`PermissionRole`/`RoleUserSchool` models and
  the `permissions`, `permission_role`, `role_user_school` tables.
  (The legacy `roles` table is kept: `teachers.teacher_role` and `users.attached_company_role` use it.)
* The `master_data` module from the **school** rights catalogue (Master Data, Master Codes, NLSC master,
  A/O-level subjects now live in Admin Rights).
* Legacy admin-user screens/routes superseded by *Admin Users*: `users-register`, `users-information`,
  `edit-specific-user`, `users/delete-user`, `store-internal-user`, `save-role`, `store-role-update`.

## Security fixes shipped with this change

* `/admin-user` and `/student-user` (credential-less login as admin #1) exist **only when `APP_ENV=local`**.
* All Schools routes now require login **and** a permission (previously no middleware at all).
* `update-internal-user` could `updateOrCreate` any user id (incl. password) for any logged-in user; it is now
  self-only unless the caller holds `admin_rights.edit_user`, and only super admins may touch super admins.
