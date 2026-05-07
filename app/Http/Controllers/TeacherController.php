<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Teacher;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;


class TeacherController extends Controller
{
    public function addTeachers()
    {
        Helper::requireSchool();

        $school_id = Helper::requireSchool();

        return view('Teacher.add-teachers', compact('school_id'));
    }

    public function storeTeacher(Request $request)
    {

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

        $teacher = Teacher::create($validated);

        return response()->json(['message' => 'Teacher added successfully']);
    }

    public function allTeachers()
    {
        $teachers = Teacher::orderBy('surname')->get();

        return view('Teacher.teachers-in-school', compact('teachers'));
    }

    public function teacherProfile($id)
    {
        Helper::requireSchool();

        $teacher = Teacher::where('school_id', Helper::requireSchool())->where('id', $id)->first();
        $school_id = Helper::requireSchool();

        return view('Teacher.teacher-profile', compact('teacher', 'school_id'));
    }

    public function updateteacherProfile($id)
    {

        Helper::requireSchool();

        $roles = Role::all();

        $teacher = Teacher::where('school_id', Helper::requireSchool())->where('id', $id)->first();

        return view('users.update-user-info', compact('teacher', 'roles'));
    }

    public function getTeacherData($id)
    {
        $teacher = Teacher::where('school_id', Helper::requireSchool())
            ->where('id', $id)
            ->firstOrFail();

        return response()->json($teacher);
    }

    public function storeUpdatedTeacherProfile(Request $request, Teacher $teacher)
    {
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
        Helper::requireSchool();

        $school_id = Helper::requireSchool();

        $teachers = Teacher::where('school_id', $school_id)
            ->orderBy('surname')
            ->get();

        // Fetch roles with scope 'school'
        $schoolRoles = Role::where('scope', 'school')
            ->orderBy('name')
            ->get();

        return view('Teacher.teachers-in-school', compact('teachers', 'school_id', 'schoolRoles'));
    }


    public function updateTeacherRole(Request $request, $id)
    {
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
        $teacher = Teacher::with('role')->findOrFail($id);

        // Add role name to the response
        $teacher->role_name = $teacher->role ? $teacher->role->name : 'Not Assigned';

        return response()->json($teacher);
    }

    public function assignTeacherRole(Request $request)
    {
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
        $teachers = Teacher::with('school')
            ->where('school_id', $schoolId)
            ->orderBy('id')
            ->get();

        $school_id = $schoolId;

        return view('Teacher.teachers-in-school', compact('teachers', 'school_id'));
    }

    public function destroyTeacher($id)
    {
        $teacher = Teacher::findOrFail($id);

        if ($teacher->teacher_profile && File::exists(public_path($teacher->teacher_profile))) {
            File::delete(public_path($teacher->teacher_profile));
        }

        $teacher->delete();

        return response()->json(['message' => 'Teacher deleted successfully.']);
    }

    public function updatePassword(Request $request)
    {
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

}
