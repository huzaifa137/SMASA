<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Teacher;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class TeacherController extends Controller
{
    public function addTeachers()
    {
        $school_id = Session('LoggedSchool');

        return view('Teacher.add-teachers', compact('school_id'));
    }

    public function storeTeacher(Request $request)
    {

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

        return view('Users.update-user-info', compact('teacher', 'roles'));
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

        $teachers = Teacher::where('school_id', Helper::requireSchool())
            ->orderBy('surname')
            ->get();

        $school_id = Helper::requireSchool();

        return view('Teacher.teachers-in-school', compact('teachers', 'school_id'));
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
}
