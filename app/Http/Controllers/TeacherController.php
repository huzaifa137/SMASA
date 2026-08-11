<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\School;
use App\Models\Teacher;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use App\Helpers\PermissionHelper;
use App\Services\TeacherDeletionService;

class TeacherController extends Controller
{
    public function addTeachers()
    {
        PermissionHelper::denyUnlessFeature('view_teachers');
        Helper::requireSchool();

        $school_id = Helper::requireSchool();

        return view('teacher.add-teachers', compact('school_id'));
    }

    public function storeTeacher(Request $request)
    {

        if (!PermissionHelper::canFeature('add_teacher')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized Access. You do not have permission to add teachers.'], 403);
        }


        if (!Helper::isTechSateAdminOrSchoolAdminsAlone()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized Access. Contact School Admin to Add new Teacher'
            ], 422);
        }

        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'surname' => 'required|string|max:255',
            'firstname' => 'required|string|max:255',
            'othername' => 'nullable|string|max:255',
            'initials' => 'nullable|string|max:255',
            'phonenumber' => 'required|string|max:20|unique:teachers',
            'registration_number' => 'nullable|string|max:50',
            'gender' => 'nullable|in:male,female',
            'national_id' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'employee_number' => 'nullable|string|max:50',
            'group_teacher' => 'nullable|integer',
            'email' => 'required|unique:teachers',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $userExistsInSchool = DB::table('teachers')
            ->where('email', $request->email)
            ->where('school_id', $request->school_id)
            ->first();

        if ($userExistsInSchool) {
            return response()->json([
                'message' => 'This teacher is already registered under this school.',
            ], 422);
        }

        $userExists = DB::table('schools')->where('email', $request->email)->first();

        if ($userExists) {
            return response()->json([
                'exists' => true,
                'message' => 'A user with this email already exists in another school.',
            ]);
        }

        // ── Login credentials ───────────────────────────────────────────
        // Teachers log in by phonenumber + password (see
        // UserController::authenticateSchool). Previously this endpoint
        // never set a password at all, so newly added teachers had no way
        // to ever log in. If the admin supplied one, hash and use it;
        // otherwise generate a temporary one and force a change on first
        // login, the same pattern used by the bulk-import flow.
        $plainPassword = $request->filled('password') ? $request->password : Str::random(10);

        unset($validated['password']);
        $validated['password'] = Hash::make($plainPassword);
        $validated['must_change_password'] = true;
        $validated['account_status'] = 'active';

        $teacher = Teacher::create($validated);

        return response()->json([
            'message' => 'Teacher added successfully',
            'temporary_password' => $request->filled('password') ? null : $plainPassword,
        ]);
    }

    public function allTeachers()
    {
        PermissionHelper::denyUnlessFeature('view_teachers');
        $teachers = Teacher::orderBy('surname')->get();

        return view('teacher.teachers-in-school', compact('teachers'));
    }

    public function teacherProfile($id)
    {
        PermissionHelper::denyUnlessFeature('view_teachers');
        Helper::requireSchool();

        $teacher = Teacher::where('school_id', Helper::requireSchool())->where('id', $id)->first();
        $school_id = Helper::requireSchool();

        return view('teacher.teacher-profile', compact('teacher', 'school_id'));
    }

    public function updateteacherProfile($id)
    {

        PermissionHelper::denyUnlessFeature('edit_teacher');
        Helper::requireSchool();

        $roles = Role::all();

        $teacher = Teacher::where('school_id', Helper::requireSchool())->where('id', $id)->first();

        return view('users.update-user-info', compact('teacher', 'roles'));
    }

    public function getTeacherData($id)
    {
        PermissionHelper::denyUnlessFeature('view_teachers');

        $teacher = Teacher::where('school_id', Helper::requireSchool())
            ->where('id', $id)
            ->firstOrFail();

        return response()->json($teacher);
    }

    public function storeUpdatedTeacherProfile(Request $request, Teacher $teacher)
    {

        if (!PermissionHelper::canFeature('edit_teacher')) {
            return response()->json(['message' => 'Unauthorized Access. You do not have permission to edit teachers.'], 403);
        }


        $validated = $request->validate([
            'surname' => 'required|string|max:255',
            'firstname' => 'required|string|max:255',
            'phonenumber' => 'required|string|max:20',
            'othername' => 'nullable|string|max:255',
            'initials' => 'nullable|string|max:255',
            'registration_number' => 'nullable|string|max:50',
            'gender' => 'nullable|in:male,female',
            'national_id' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'employee_number' => 'nullable|string|max:50',
            'group_teacher' => 'nullable|integer|between:1,5',
            'teacher_profile' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $profile = Teacher::where('id', $teacher->id)->first();

        if ($request->hasFile('teacher_profile')) {

            if ($profile && $profile->teacher_profile) {
                Storage::disk('public')->delete($profile->teacher_profile);
            }

            $logoFile = $request->file('teacher_profile');
            $logoPath = $logoFile->store('teacherProfiles', 'public');
            $validated['teacher_profile'] = $logoPath;
        } elseif ($profile) {
            $validated['teacher_profile'] = $profile->teacher_profile;
        } else {
            $validated['teacher_profile'] = null;
        }

        $teacher->update($validated);

        return response()->json(['message' => 'Teacher updated successfully']);
    }

    public function schoolTeachers()
    {
         PermissionHelper::denyUnlessFeature('view_teachers');
    Helper::requireSchool();

        $school_id = Helper::requireSchool();

        $teachers = Teacher::where('school_id', $school_id)
            ->orderBy('surname')
            ->get();

        // Fetch roles with scope 'school'
        $schoolRoles = Role::where('scope', 'school')
            ->orderBy('name')
            ->get();

        return view('teacher.teachers-in-school', compact('teachers', 'school_id', 'schoolRoles'));
    }


    public function updateTeacherRole(Request $request, $id)
    {
         if (!PermissionHelper::canFeature('edit_teacher')) {
        return response()->json(['status' => false, 'message' => 'Unauthorized Access. You do not have permission to edit teachers.'], 403);
    }

        try {
            $teacher = Teacher::findOrFail($id);

            // Verify school authorization

            Helper::requireSchool();

            // Optional permission check
            if (!Helper::isTechSateAdminOrSchoolAdminsOrTechSateSalesRepresentatives()) {

                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized Access. Contact School Admin to assign Role.'
                ], 403);
            }

            if ($teacher->school_id != Helper::requireSchool()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $request->validate([
                'role_id' => 'required|exists:roles,id'
            ]);

            $teacher->teacher_role = $request->role_id;
            $teacher->save();

            return response()->json([
                'success' => true,
                'message' => 'Teacher role updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating teacher role: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getTeacherProfileData($id)
    {
        PermissionHelper::denyUnlessFeature('view_teachers');
        $teacher = Teacher::with('role')->findOrFail($id);

        // Add role name to the response
        $teacher->role_name = $teacher->role ? $teacher->role->name : 'Not Assigned';

        return response()->json($teacher);
    }

    public function assignTeacherRole(Request $request)
    {
         if (!PermissionHelper::canFeature('edit_teacher')) {
        return response()->json(['message' => 'Unauthorized Access. You do not have permission to edit teachers.'], 403);
    }

        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'role_id' => 'required|exists:roles,id',
            'alias' => 'nullable|string|max:100',
        ]);

        $teacher = Teacher::findOrFail($request->teacher_id);

        // Ensure teacher belongs to this school
        if ($teacher->school_id !== Helper::requireSchool()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $teacher->teacher_role = $request->role_id;
        $teacher->group_teacher = $request->alias; // re-uses group_teacher column for alias
        $teacher->save();

        return response()->json(['message' => 'Role assigned successfully', 'teacher' => $teacher]);
    }

    public function individualSchoolTeachers($schoolId)
    {
        PermissionHelper::denyUnlessFeature('view_teachers');
        $teachers = Teacher::with('school')
            ->where('school_id', $schoolId)
            ->orderBy('id')
            ->get();

        $school_id = $schoolId;

        return view('teacher.teachers-in-school', compact('teachers', 'school_id'));
    }

    public function destroyTeacher($id, TeacherDeletionService $deletionService)
    {
        if (!PermissionHelper::canFeature('delete_teacher')) {
            return response()->json(['message' => 'Unauthorized Access. You do not have permission to delete teachers.'], 403);
        }

        $teacher = Teacher::findOrFail($id);

        $result = $deletionService->deleteTeacher($teacher);

        return response()->json(['message' => $result['message']], $result['success'] ? 200 : 500);
    }

    /**
     * Bulk-delete teachers by an explicit list of IDs. Own records
     * (attendance, payroll, id card, school-role) are removed; anything
     * they entered/took/received on behalf of a student (marks,
     * attendance, fee payments) is preserved and just detached — see
     * TeacherDeletionService.
     */
    public function bulkDestroyTeachers(Request $request, TeacherDeletionService $deletionService)
    {
        if (!PermissionHelper::canFeature('delete_teacher')) {
            return response()->json(['message' => 'Unauthorized Access. You do not have permission to delete teachers.'], 403);
        }

        $schoolId = Helper::requireSchool();

        $validated = $request->validate([
            'teacher_ids'   => 'required|array|min:1',
            'teacher_ids.*' => 'integer|exists:teachers,id',
        ]);

        $teacherIds = Teacher::whereIn('id', $validated['teacher_ids'])
            ->where('school_id', $schoolId)
            ->pluck('id')
            ->all();

        if (empty($teacherIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No matching teachers found to delete.',
            ], 404);
        }

        $result = $deletionService->deleteTeachers($teacherIds);

        return response()->json([
            'success' => $result['failed'] === 0,
            'message' => "{$result['deleted']} teacher(s) deleted." . ($result['failed'] ? " {$result['failed']} failed." : ''),
            'deleted' => $result['deleted'],
            'failed'  => $result['failed'],
            'errors'  => $result['errors'],
        ], $result['failed'] === 0 ? 200 : 207);
    }

    public function updatePassword(Request $request)
    {

    //  if (!PermissionHelper::canFeature('edit_teacher')) {
    //     return response()->json(['status' => false, 'message' => 'Unauthorized Access. You do not have permission to edit teachers.'], 403);
    // }

        try {
            // Validate request
            $request->validate([
                // 'password' => 'required|min:8|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*#?&]/',
                'password' => 'required',
                'teacher_id' => 'required|exists:teachers,id'
            ], [
                'password.regex' => 'Password must contain at least one uppercase letter, one number, and one special character.'
            ]);

            // Find the teacher
            $teacher = Teacher::findOrFail($request->teacher_id);

            // Update password
            $teacher->password = Hash::make($request->password);
            $teacher->must_change_password = false; // Set to false after password change
            $teacher->save();

            // Update session if needed
            Session::put('LoggedTeacher', $teacher->id);

            return response()->json([
                'status' => true,
                'message' => 'Password updated successfully! You can now continue using the system.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }


    // ─────────────────────── BULK IMPORT – TEACHERS ───────────────────────

    public function bulkImportTeacherForm()
    {
            PermissionHelper::denyUnlessFeature('add_teacher');
    Helper::requireSchool();

        $schoolId = Helper::requireSchool();
        $school = \App\Models\School::findOrFail($schoolId);

        return view('teacher.bulk-import-teachers', compact('schoolId', 'school'));
    }

    public function downloadTeacherTemplate()
    {
        PermissionHelper::denyUnlessFeature('add_teacher');
    Helper::requireSchool();
        $schoolId = Helper::requireSchool();
        $schoolName = DB::table('schools')->where('id', $schoolId)->value('name') ?? 'School';
        $filename = 'teachers_import_' . preg_replace('/\s+/', '_', $schoolName) . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\TeacherBulkTemplate($schoolName),
            $filename
        );
    }

    public function bulkImportTeachers(Request $request)
    {

     if (!PermissionHelper::canFeature('add_teacher')) {
        return response()->json(['status' => 'error', 'message' => 'Unauthorized Access. You do not have permission to add teachers.'], 403);
    }


        if (!Helper::isTechSateAdminOrSchoolAdminsAlone()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized Access.'], 403);
        }

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        $schoolId = Helper::requireSchool();
        $addedBy = session('LoggedTeacher') ?? session('LoggedAdmin');

        $importer = new \App\Imports\TeacherBulkImport($schoolId, $addedBy);
        \Maatwebsite\Excel\Facades\Excel::import($importer, $request->file('file'));

        return response()->json([
            'status' => 'success',
            'imported' => $importer->importedCount,
            'skipped' => $importer->skippedCount,
            'errors' => $importer->errors,
            'message' => $importer->importedCount . ' teacher(s) imported.'
                . ($importer->skippedCount ? ' ' . $importer->skippedCount . ' skipped (already exist).' : '')
                . (count($importer->errors) ? ' ' . count($importer->errors) . ' row(s) had errors.' : ''),
        ]);
    }

    // ─────────────────────── TEACHER ACCOUNT STATUS ───────────────────────

    public function updateTeacherStatus(Request $request, $id)
    {

        if (!PermissionHelper::canFeature('manage_teacher_status')) {
        return response()->json(['status' => false, 'message' => 'Unauthorized Access. You do not have permission to manage teacher status.'], 403);
    }

        if (!Helper::isTechSateAdminOrSchoolAdminsAlone()) {
            return response()->json(['status' => false, 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'account_status' => 'required|in:active,suspended,blocked',
            'status_reason' => 'nullable|string|max:500',
        ]);

        $teacher = \App\Models\Teacher::findOrFail($id);

        if ($teacher->school_id !== Helper::requireSchool()) {
            return response()->json(['status' => false, 'message' => 'Unauthorized.'], 403);
        }

        $teacher->account_status = $request->account_status;
        $teacher->status_reason = $request->status_reason;
        $teacher->status_changed_at = now();
        $teacher->status_changed_by = session('LoggedAdmin') ?? session('LoggedTeacher');
        $teacher->save();

        $label = ucfirst($request->account_status);

        return response()->json([
            'status' => true,
            'message' => "Teacher account has been {$label}.",
            'new_status' => $request->account_status,
        ]);
    }

    public function getTeacherStatus($id)
    {
        PermissionHelper::denyUnlessFeature('view_teachers');
        
        $teacher = \App\Models\Teacher::where('school_id', Helper::requireSchool())
            ->where('id', $id)
            ->firstOrFail();

        return response()->json([
            'account_status' => $teacher->account_status ?? 'active',
            'status_reason' => $teacher->status_reason,
            'status_changed_at' => $teacher->status_changed_at,
        ]);
    }
}