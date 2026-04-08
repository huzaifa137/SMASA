<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\DynamicFormValue;
use App\Models\House;
use App\Models\MasterData;
use App\Models\School;
use App\Models\SchoolProfile;
use App\Models\TermDate;
use App\Models\UpdateTracker;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Session;

class SchoolController extends Controller
{
    public function adminUser(Request $request)
    {
        session()->flush();
        $request->session()->put('LoggedAdmin', 1);

        $request->session()->put('LoggedSchool', 457);

        return view('dashboard');
    }

    public function studentUser(Request $request)
    {
        session()->flush();
        $request->session()->put('LoggedStudent', 1);
        $request->session()->put('LoggedAdmin', 1);
        $request->session()->put('LoggedSchool', 2);

        return view('student.dashboard');
    }

    public function createSchool()
    {

        $year = date('Y');

        $lastSchool = School::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastSchool) {
            $lastNumber = (int) substr($lastSchool->registration_code, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        $registrationCode = $this->generateSchoolCode();

        return view('School.create-school', compact('registrationCode'));
    }

    public function allSchools()
    {
        $schools = House::orderBy('id', 'Desc')->get();

        return view('School.all-schools', compact('schools'));
    }

    public function changeStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:0,8,9,10,1',
        ]);

        $HouseID = House::where('ID', $id)->value('Number');
        $schoolID = School::where('registration_code', $HouseID)->value('id');
        $id = $schoolID;

        $school = School::findOrFail($id);
        $school->school_status = $request->status;
        $school->save();

        $teacherIds = DB::table('teachers')
            ->where('school_id', $id)
            ->pluck('id');

        foreach ($teacherIds as $teacherId) {

            $username = (string) $teacherId;

            $user = DB::table('users')->where('username', $username)->first();

            if ($user) {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['account_status' => $request->status]);
            }
        }

        return response()->json([
            'message' => 'School and teacher statuses updated successfully.',
        ]);
    }

    public function termDates($school_Id, Request $request)
    {
        $school_id = $school_Id;

        $academicYears = AcademicYear::orderBy('id', 'desc')->get();

        $activeYear = AcademicYear::where('is_active', 1)->first();
        $selectedYearId = $request->year ?? ($activeYear->id ?? null);

        $termDates = TermDate::where('school_id', $school_id)
            ->where('academic_year_id', $selectedYearId)
            ->orderBy('term', 'asc')
            ->get();

        return view('School.term-dates', compact(
            'school_id',
            'academicYears',
            'termDates',
            'selectedYearId'
        ));
    }

    public function createNewSchool(Request $request)
    {
        $validated = $request->validate([
            'school_type' => 'required|string|max:255',
            'email' => 'required|email',
            'gender' => 'required|string|max:50',
            'regional_level' => 'required|string|max:100',
            'school_ownership' => 'required|string|max:100',
            'boarding_status' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'school_product' => 'required',
            'phone' => 'required|string|max:20',
            'population' => 'required|string',
            'school_name_arabic' => 'nullable|string|max:255',
        ]);

        // Generate school registration code
        $registrationCode = $this->generateSchoolCode();

        $validated['registration_code'] = $registrationCode;
        $validated['added_by'] = Session('LoggedStudent');
        $validated['date_added'] = now();

        // Create new school
        $school = School::create($validated);

        // Create corresponding house
        DB::table('houses')->insert([
            'House' => $validated['name'],
            'House_AR' => $validated['school_name_arabic'] ?? '',
            'Number' => $registrationCode,
            'Location' => 'Kampala',
            'RegistrationDate' => now(),
            'Head' => 0,
            'ContactPerson' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'School created successfully and house added.',
            'registration_code' => $registrationCode,
        ]);
    }

    private function generateSchoolCode()
    {
        $year = date('Y');

        // Get all schools created this year and only with proper 'SCH-' codes
        $lastSchool = School::whereYear('created_at', $year)
            ->where('registration_code', 'like', 'SCH-'.$year.'-%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastSchool && $lastSchool->registration_code) {
            // Extract numeric part at the end
            preg_match('/(\d+)$/', $lastSchool->registration_code, $matches);
            $lastNumber = isset($matches[1]) ? (int) $matches[1] : 0;

            $newNumber = $lastNumber + 1;
        } else {
            // No valid records yet, start at 1
            $newNumber = 1;
        }

        $formattedNumber = str_pad($newNumber, 4, '0', STR_PAD_LEFT);

        return "SCH-{$year}-{$formattedNumber}";
    }

    public function editSchool($id)
    {
        $HouseID = House::where('ID', $id)->value('Number');

        $schoolID = School::where('registration_code', $HouseID)->value('id');
        $school = School::where('id', $schoolID)->first();
        $school_id = $schoolID;

        return view('School.edit-school', compact(['school', 'school_id']));
    }

    public function updateSchool(Request $request)
    {
        $school = School::findOrFail($request->school_id);

        $validated = $request->validate([
            'school_type' => 'required|string|max:255',
            'email' => 'required|email',
            'gender' => 'required|string|max:50',
            'regional_level' => 'required|string|max:100',
            'school_ownership' => 'required|string|max:100',
            'boarding_status' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'school_product' => 'required',
            'registration_code' => 'required|string|max:50',
            'phone' => 'required|string|max:20',
            'population' => 'required|string',
        ]);

        $school->update($validated);

        UpdateTracker::create([
            'item_id' => $request->school_id,
            'item_category' => 'School Information Updated',
            'updated_by' => session('LoggedStudent'),
            'date_updated_on' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'School Information updated successfully.',
        ]);
    }

    public function deleteSchool(School $schoolId)
    {
        try {
            SchoolProfile::where('school_id', $schoolId->id)->delete();
            House::where('Number', $schoolId->registration_code)->delete();
            $schoolId->delete();

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Delete failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function schoolIndividualProfile($id)
    {

        $HouseID = House::where('ID', $id)->value('Number');

        $schoolID = School::where('registration_code', $HouseID)->value('id');
        $school = School::where('id', $schoolID)->first();
        $profile = SchoolProfile::where('school_id', $schoolID)->first();

        return view('School.school-profile', compact('school', 'profile'));
    }

    public function schoolProfile()
    {
        if (Session::has('LoggedSchool') && Session::get('LoggedSchool') !== null) {

            $school = School::findOrFail(Session('LoggedSchool'));
            $profile = SchoolProfile::where('school_id', Session('LoggedSchool'))->first();

            return view('School.school-profile', compact('school', 'profile'));

        } else {
            return redirect()->route('student.dashboard')->with('error', 'No School has been selected');
        }
    }

    public function schoolOptions($id)
    {
        $school = School::findOrFail($id);
        $profile = SchoolProfile::where('school_id', $id)->first();

        $genderMasterDataCollection = MasterData::where('md_master_code_id', config('constants.options.SCHOOL_OPTIONALS'))->get();

        $allDynamicFields = collect();
        $masterDataDetails = collect();

        if ($genderMasterDataCollection->isNotEmpty()) {
            foreach ($genderMasterDataCollection as $masterData) {
                $masterDataId = $masterData->md_id;

                $dynamicFieldsForThisMasterData = DynamicFormValue::where('master_data_id', $masterDataId)->get();

                $allDynamicFields = $allDynamicFields->merge($dynamicFieldsForThisMasterData);

                $masterDataDetails->push([
                    'name' => $masterData->md_name,
                    'description' => $masterData->md_description ?? 'N/A',
                ]);
            }
        }

        return view('School.school-options', compact(
            'school',
            'profile',
            'masterDataDetails',
            'allDynamicFields'
        ));
    }

    public function storeSchoolProfile(Request $request)
    {
        $validated = $request->validate([
            'school_id' => 'required|integer|exists:schools,id',
            'school_type' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'gender' => 'required|string|max:50',
            'boarding_status' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'registration_code' => 'required|string|max:50',
            'phone' => 'required|string|max:20',
            'population' => 'required|string',
            'motto' => 'nullable|string|max:255',
            'vision' => 'nullable|string|max:255',
            'admission_prefix' => 'nullable|string|max:50',
            'admission_start' => 'nullable|string|max:50',
            'admission_suffix' => 'nullable|string|max:50',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $profile = SchoolProfile::where('school_id', $validated['school_id'])->first();

        if ($request->hasFile('logo')) {

            if ($profile && $profile->logo) {
                Storage::disk('public')->delete($profile->logo);
            }

            $logoFile = $request->file('logo');
            $logoPath = $logoFile->store('logos', 'public');
            $validated['logo'] = $logoPath;
        } elseif ($profile) {
            $validated['logo'] = $profile->logo;
        } else {
            $validated['logo'] = null;
        }

        $validated['updated_at'] = now();

        if ($profile) {
            $profile->update($validated);
            $message = 'School profile updated successfully.';
        } else {
            $validated['created_at'] = now();
            SchoolProfile::create($validated);
            $message = 'School profile created successfully.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    public function configureSchoolOptions(Request $request)
    {
        dd($request->all());
    }

    public function addAcademicYear()
    {

        $academicYears = AcademicYear::orderBy('id', 'desc')->get();

        return view('AcademicYear.add-year', compact(['academicYears']));
    }

    // public function storeYear(Request $request)
    // {
    //     $validated = $request->validate([
    //         'name' => 'required|string|max:255|unique:academic_years,name',
    //         'start_date' => 'required|date',
    //         'end_date' => 'required|date|after_or_equal:start_date',
    //         'is_active' => 'required|boolean',
    //     ]);

    //     $academicYear = AcademicYear::create($validated);

    //     return response()->json([
    //         'message' => 'Academic Year created successfully.',
    //         'data' => $academicYear,
    //     ], 201);
    // }

    public function storeYear(Request $request)
    {
        $currentYear = date('Y');
        $nextYear = $currentYear + 1;

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^\d{4}$/', // Only 4-digit year
                'unique:academic_years,name',
                function ($attribute, $value, $fail) use ($currentYear, $nextYear) {
                    if ($value < $currentYear) {
                        $fail('Cannot create academic year for past years. Year must be current or future.');
                    }
                    if ($value > $nextYear) {
                        $fail('Can only create academic year for '.$nextYear.' at maximum.');
                    }
                },
            ],
            'start_date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) use ($request) {
                    $year = $request->name;
                    $startDate = date('Y-m-d', strtotime($value));
                    $expectedStart = $year.'-01-01';
                    $expectedEnd = $year.'-12-31';

                    if ($startDate < $expectedStart || $startDate > $expectedEnd) {
                        $fail("Start date must be within the year {$year} (Jan 1 - Dec 31).");
                    }
                },
            ],
            'end_date' => [
                'required',
                'date',
                'after_or_equal:start_date',
                function ($attribute, $value, $fail) use ($request) {
                    $year = $request->name;
                    $endDate = date('Y-m-d', strtotime($value));
                    $expectedStart = $year.'-01-01';
                    $expectedEnd = $year.'-12-31';

                    if ($endDate < $expectedStart || $endDate > $expectedEnd) {
                        $fail("End date must be within the year {$year} (Jan 1 - Dec 31).");
                    }
                },
            ],
            'is_active' => 'required|boolean',
        ]);

        // Auto-set dates based on the year if not provided or override
        $year = $validated['name'];
        $validated['start_date'] = $year.'-01-01';
        $validated['end_date'] = $year.'-12-31';

        // Handle active status logic - only one active year at a time
        if ($validated['is_active'] == 1) {
            // Deactivate all other academic years
            AcademicYear::where('is_active', 1)->update(['is_active' => 0]);
        }

        $academicYear = AcademicYear::create($validated);

        return response()->json([
            'message' => 'Academic Year '.$year.' created successfully.',
            'data' => $academicYear,
        ], 201);
    }

    public function activate($id)
    {
        AcademicYear::query()->update(['is_active' => false]); // Deactivate all
        $year = AcademicYear::findOrFail($id);
        $year->update(['is_active' => true]);

        return response()->json(['message' => 'Academic year activated.']);
    }

    public function deactivate($id)
    {
        $year = AcademicYear::findOrFail($id);
        $year->update(['is_active' => false]);

        return response()->json(['message' => 'Academic year deactivated.']);
    }

    public function destroy($id)
    {
        dd($id);
        $academicYear = AcademicYear::findOrFail($id);

        if ($academicYear->is_active) {
            return response()->json(['error' => 'Cannot delete an active academic year.'], 403);
        }

        $academicYear->delete();

        return response()->json(['message' => 'Academic year deleted successfully.']);
    }

    public function updateYear(Request $request, $id)
    {
        $academicYear = AcademicYear::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:academic_years,name,'.$id,
            'is_active' => 'required|boolean',
        ]);

        if ($request->is_active == 1) {
            AcademicYear::query()->update(['is_active' => false]);
            $year = AcademicYear::findOrFail($id);
            $year->update(['is_active' => true]);
        }

        $academicYear->update($validated);

        return response()->json([
            'message' => 'Academic Year updated successfully.',
            'data' => $academicYear,
        ]);
    }

    public function storeTermDate(Request $request)
    {

        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'term' => 'required|string|max:255',
            'start_date' => 'required|date',
            'school_id' => 'required',
            'end_date' => 'required|date|after_or_equal:start_date',
            'week_starts_on' => 'required|in:1,2',
        ]);

        $exists = TermDate::where('school_id', $validated['school_id'])
            ->where('term', $validated['term'])
            ->where('academic_year_id', $validated['academic_year_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'This term already exists for the selected school.',
            ], 409);
        }

        $termDate = TermDate::create($validated);

        return response()->json([
            'message' => 'Term date added successfully.',
            'data' => $termDate,
        ], 201);
    }

    public function destroyTerm($id)
    {
        $academicTerm = TermDate::findOrFail($id);
        $academicTerm->delete();

        return response()->json(['message' => 'Term deleted successfully.']);
    }

    public function selectSchool(Request $request)
    {

        $schoolId = $request->input('school_id');
        $school = School::find($schoolId);
        // dd($school);
        if (! $school) {
            return response()->json([
                'status' => false,
                'message' => 'School not found.',
            ]);
        }

        $request->session()->put('LoggedSchool', $schoolId);

        return response()->json([
            'status' => true,
            'message' => "School switched to {$school->name}",
        ]);
    }

    public function clearSchool(Request $request)
    {
        if ($request->session()->has('LoggedSchool')) {
            $request->session()->forget('LoggedSchool'); // remove the session

            return response()->json([
                'status' => true,
                'message' => 'Selected school has been cleared.',
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'No school is currently selected.',
            ]);
        }
    }

    public function toggleActive(Request $request)
    {
        try {
            $request->validate([
                'term_id' => 'required|exists:term_dates,id',
                'is_active' => 'required|boolean',
            ]);

            $termDate = TermDate::findOrFail($request->term_id);
            $schoolId = Session::get('LoggedSchool');

            // Verify school ownership
            if ($termDate->school_id != $schoolId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access',
                ], 403);
            }

            // If trying to activate this term
            if ($request->is_active == 1) {
                // Deactivate all other active terms for this school and academic year
                TermDate::where('school_id', $schoolId)
                    ->where('academic_year_id', $termDate->academic_year_id)
                    ->where('is_active', 1)
                    ->update(['is_active' => 0]);

                // Activate this term
                $termDate->is_active = 1;
                $termDate->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Term activated successfully',
                    'term_id' => $termDate->id,
                ]);
            }
            // If trying to deactivate this term
            else {
                $termDate->is_active = 0;
                $termDate->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Term deactivated successfully',
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: '.$e->getMessage(),
            ], 500);
        }
    }
}
