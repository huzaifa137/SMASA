<?php

namespace App\Http\Controllers;

use App\Exports\StudentsExamExport;
use App\Imports\StudentExamImport;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\Grading;
use App\Models\House;
use App\Models\MasterData;
use App\Models\School;
use App\Models\Stream;
use App\Models\Student;
use App\Models\StudentBasic;
use App\Models\Subject;
use App\Models\User;
use App\Services\GradingService;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Mail;

class StudentController extends Controller
{
    public function register(Request $request)
    {
        return view('users.register');
    }

    public function user_terms_and_conditions(Request $request)
    {
        return view('users.terms-and-conditions');
    }

    public function flushSession()
    {
        session()->flush();

        return redirect('/');
    }

    public function userRegistrationOTP()
    {
        return view('users.LoginOTP');
    }

    public function userAccountCreation(Request $request)
    {

        // ACCOUNT STATUSES
        // --------------------------------------
        // 1.Banned     ====> 0
        // 2.Locked     ====> 8
        // 3.Suspended  ====> 9
        // 4.Active     ====> 10

        $request->validate([
            'username' => 'required',
            'email' => 'required|email|unique:users',
            'password' => [
                'required',
                'string',
                'min:6',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*?&#]/',
            ],
        ], [
            'password.required' => 'The password field is required.',
            'password.string' => 'The password must be a string.',
            'password.min' => 'The password must be at least 6 characters.',
            'password.regex' => 'The password must include at least one uppercase letter, one lowercase letter, one digit, and one special character.',
        ]);

        $password = $request->password;
        $confirm_password = $request->confirmPassword;

        if ($password != $confirm_password) {
            return response()->json([
                'status' => false,
                'message' => 'Provided Passwords do not match',
            ]);
        }

        $user = new User;

        $user->username = $request->username;
        $user->email = $request->email;
        $user->password = Hash::make($password);
        $save = $user->save();

        $generatedOTP = rand(10000, 99999);
        $info = DB::table('users')->where('email', $request->email)->update(['temp_otp' => $generatedOTP]);
        $registeredUser = DB::table('users')->where('email', $request->email)->first();

        if ($registeredUser && Hash::check($request->password, $registeredUser->password)) {

            $generatedOTP = rand(10000, 99999);
            DB::table('users')->where('email', $request->email)->update(['temp_otp' => $generatedOTP]);

            $userId = $registeredUser->id;
            $username = $registeredUser->username;
            $useremail = $registeredUser->email;

            $data = [
                'subject' => 'Idaad & Thanawi Exam System REGISTRATION OTP',
                'body' => 'Enter the Sent OTP to confirm registration : ',
                'generatedOTP' => $generatedOTP,
                'username' => $username,
                'email' => $useremail,
            ];

            try {
                Mail::send('emails.otp', $data, function ($message) use ($data) {
                    $message->to($data['email'], $data['email'])->subject($data['subject']);
                });
            } catch (Exception $e) {
                DB::table('users')->where('email', $request->email)->delete();

                return back()->with('error', 'Email Not, Check Internet or re-register');
            }

            $request->session()->put('userId', $userId);
            $request->session()->put('userEmail', $useremail);
            $request->session()->put('userPassword', $request->password);

            return response()->json([
                'status' => true,
                'message' => 'OTP has been sent,check your email to proceed',
                'redirect_url' => '/users/user-otp',
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'There was something wrong in creating this account,try registering again or contact admins',
                'redirect_url' => '/',
            ]);
        }
    }

    public function supplierOtpVerification(Request $request)
    {

        $otp_1 = $request->input('otp_1');
        $otp_2 = $request->input('otp_2');
        $otp_3 = $request->input('otp_3');
        $otp_4 = $request->input('otp_4');
        $otp_5 = $request->input('otp_5');

        $new_otp = $otp_1.$otp_2.$otp_3.$otp_4.$otp_5;
        $user_id = $request->input('hidden_otp');

        $temp_otp_stored = DB::table('users')->where('id', $user_id)->value('temp_otp');
        $supplier_username = DB::table('users')->where('id', $user_id)->value('username');
        $userRole = DB::table('users')->where('id', $user_id)->value('user_role');

        if ($new_otp == $temp_otp_stored) {

            if ($userRole != 1) {
                $request->session()->put('LoggedAdmin', $user_id);
            } else {
                $request->session()->put('LoggedStudent', $user_id);
            }

            $url = '/';
            $url2 = session()->get('url.intended');
            $url3 = '/student/dashboard';

            if ($userRole != 1) {
                if ($url2 != null) {
                    return response()->json([
                        'status' => true,
                        'message' => 'Login successful',
                        'redirect_url' => $url2,
                    ]);
                }

                return response()->json([
                    'status' => true,
                    'message' => 'Login successful',
                    'redirect_url' => $url,
                ]);
            } else {
                return response()->json([
                    'status' => true,
                    'message' => 'Login successful',
                    'redirect_url' => $url3,
                ]);
            }
        } else {

            return response()->json([
                'status' => false,
                'title' => 'Invalid OTP',
                'message' => 'Entered OTP is invalid, please check your email for correct OTP code',
            ]);
        }
    }

    public function adminDashboard(GradingService $gradingService)
    {
        return view('student.admin-dashboard');
    }

    private function getSubjectIdsForCategory($category)
    {
        return Subject::where('category', $category)->pluck('id')->toArray();
    }

    public function selectCurrentSchool()
    {

        if (session('login_email')) {

            $email = session('login_email');
            $user = DB::table('users')->where('email', $email)->first();
            $userInfo = DB::table('teachers')->where('id', $user->username)->first();

            $teacherSchools = DB::table('teachers')
                ->where('email', session(key: 'login_email'))
                ->pluck('school_id')
                ->unique();

            $schoolsInExistance = DB::table('schools')
                ->whereIn('id', $teacherSchools)
                ->get()
                ->map(function ($school) {
                    $profile = DB::table('school_profiles')->where('school_id', $school->id)->first();
                    $school->profile = $profile;

                    return $school;
                });

            return view('users.schools-teacher-belongs-in', compact(['schoolsInExistance', 'userInfo', 'email']));
        } else {
            session()->flush();

            return redirect('/');
        }
    }

    public function studentProfile(Request $request)
    {
        $user = DB::table('users')->where('id', session('LoggedStudent'))->first();

        return view('student.profile', compact(['user']));
    }

    public function editStudentProfile()
    {

        $info = DB::table('users')->where('id', Session('LoggedStudent'))->first();

        return view('student.edit-profile', compact(['info']));
    }

    public function studentPortal()
    {
        $school_id = Session('LoggedSchool');

        $classRecord = Helper::MasterRecordMerge(
            config('constants.options.O_LEVEL'),
            config('constants.options.A_LEVEL')
        );

        $StreamRecord = Stream::where('school_id', $school_id)->get();

        $schools = School::select('id', 'name')->get();

        return view(
            'student.student-portal',
            compact('school_id', 'classRecord', 'StreamRecord', 'schools')
        );
    }

    public function addNewStudent()
    {
        Helper::requireSchool();

        $years = StudentBasic::selectRaw('DISTINCT SUBSTRING_INDEX(Student_ID, "-", -1) as year')
            ->whereRaw('Student_ID REGEXP ".*-[0-9]{4}$"')
            ->orderBy('year', 'desc')
            ->pluck('year');

        $schools = House::select('ID', 'House', 'Number')->get();

        $defaultSchoolNumber = $schools->first() ? $schools->first()->Number : 'IT-001';
        $currentYear = date('Y');
        $newStudentId = $defaultSchoolNumber.'-ID-001-'.$currentYear;

        // $classRecord = Helper::MasterRecordMerge(
        //     config('constants.options.O_LEVEL'),
        //     config('constants.options.A_LEVEL')
        // );

        $classRecord = Helper::MasterDataRecords(
            config('constants.options.O_LEVEL'),
            // config('constants.options.A_LEVEL')
        );

        return view('student.add-new-student', compact('schools', 'years', 'newStudentId', 'classRecord'));
    }

    public function generateStudentID(Request $request)
    {
        $schoolId = $request->school_id;
        $category = $request->category;
        $year = $request->year;

        if (! $schoolId || ! $category || ! $year) {
            return response()->json(['student_id' => ''], 200);
        }

        $school = DB::table('houses')->where('ID', $schoolId)->first();
        if (! $school) {
            return response()->json(['student_id' => ''], 200);
        }

        $schoolNumber = $school->Number;

        $lastNumber = DB::table('students_basic')
            ->where('Student_ID', 'LIKE', $schoolNumber.'-'.$category.'-%-'.$year)
            ->selectRaw("
            MAX(
                CAST(
                    SUBSTRING_INDEX(
                        SUBSTRING_INDEX(Student_ID, '-', 4),
                        '-', 
                        -1
                    ) AS UNSIGNED
                )
            ) as max_number
        ")
            ->value('max_number');

        $newNumber = str_pad(($lastNumber ?? 0) + 1, 3, '0', STR_PAD_LEFT);

        $newStudentID = $schoolNumber.'-'.$category.'-'.$newNumber.'-'.$year;

        return response()->json(['student_id' => $newStudentID]);
    }

    public function storeStudent(Request $request)
    {

        $validated = $request->validate([
            'School' => 'required|integer|exists:houses,ID',
            'Category' => 'required|string|max:10',
            'Admission_Year' => 'required|integer',
            'Student_ID' => 'required|string|max:25|unique:students,registration_number',
            'firstname' => 'required|string|max:100',
            'lastname' => 'required|string|max:100',
            'gender' => 'required|string|in:Male,Female,Other',
            'date_of_birth' => 'nullable|date',
            'primary_contact' => 'nullable|string|max:20',
            'other_contact' => 'nullable|string|max:20',
            'student_photo' => 'nullable|image|mimes:jpg,png,gif',
        ]);

        DB::beginTransaction();

        try {

            $photoPath = null;
            if ($request->hasFile('student_photo')) {
                $file = $request->file('student_photo');

                $destinationPath = public_path('uploads/studentPhotos');

                if (! file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                $filename = time().'_'.$file->getClientOriginalName();

                $file->move($destinationPath, $filename);

                $photoPath = 'uploads/studentPhotos/'.$filename;
            }

            // Create student record
            $student = Student::create([
                'firstname' => $validated['firstname'],
                'lastname' => $validated['lastname'],
                'senior' => $validated['Category'],
                'stream' => $request->input('stream', null), // optional
                'registration_number' => $validated['Student_ID'], // or null
                'admission_number' => $request->input('Admission_Number', null),
                'gender' => $validated['gender'],
                'school_id' => $validated['School'],
                'primary_contact' => $validated['primary_contact'] ?? null,
                'other_contact' => $validated['other_contact'] ?? null,
                'student_photo' => $photoPath,
                'date_of_admission' => $request->input('date_of_admission', null),
                'ple_score' => $request->input('ple_score', null),
                'uce_score' => $request->input('uce_score', null),
                'previous_school' => $request->input('previous_school', null),
                'primary_school_name' => $request->input('primary_school_name', null),
                'guardian_names' => $request->input('guardian_names', null),
                'relation' => $request->input('relation', null),
                'guardian_phone' => $request->input('guardian_phone', null),
                'guardian_email' => $request->input('guardian_email', null),
                'home_address' => $request->input('home_address', null),
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'place_of_birth' => $request->input('place_of_birth', null),
                'birth_certificate_entry_number' => $request->input('birth_certificate_entry_number', null),
                'nationality' => $request->input('StudentNationality', null),
                'medical_history' => $request->input('medical_history', null),
                'comments' => $request->input('comments', null),
                'added_by' => session('LoggedTeacher') ?? session('LoggedAdmin'), // assuming you want to track who added
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Student added successfully!',
                'student_id' => $student->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function allStudents()
    {
        Helper::requireSchool();

        $students = Student::where('school_id', Helper::requireSchool())
            ->orderBy('senior')
            ->orderBy('stream')
            ->select(
                'id',
                'firstname',
                'lastname',
                'gender',
                'student_photo',
                'senior',
                'stream'
            )
            ->get();

        $groupedStudents = $students->groupBy('senior')->map(function ($seniorGroup) {
            return $seniorGroup->groupBy('stream');
        });

        return view('student.all-students', compact('groupedStudents'));
    }

    public function viewStudent($id)
    {
        $student = Student::findOrFail($id);

        return response()->json([
            'student' => $student,
        ]);
    }

    public function updateStudent(Request $request, $id)
    {

        $student = Student::findOrFail($id);

        $validated = $request->validate([
            'firstname' => 'required|string|max:100',
            'lastname' => 'required|string|max:100',
            'gender' => 'required|string|in:Male,Female,Other',
            'date_of_birth' => 'nullable|date',
            'student_photo' => 'nullable|image|mimes:jpg,png,gif',
        ]);

        if ($request->hasFile('student_photo')) {
            // Delete old photo if exists
            if ($student->student_photo && file_exists(public_path($student->student_photo))) {
                unlink(public_path($student->student_photo));
            }
            $file = $request->file('student_photo');
            $destinationPath = public_path('uploads/studentPhotos');
            if (! file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            $filename = time().'_'.$file->getClientOriginalName();
            $file->move($destinationPath, $filename);
            $validated['student_photo'] = 'uploads/studentPhotos/'.$filename;
        }

        $student->update(array_merge($validated, [
            'place_of_birth' => $request->place_of_birth,
            'nationality' => $request->nationality,
            'birth_certificate_entry_number' => $request->birth_certificate_entry_number,
            'registration_number' => $request->registration_number,
            'admission_number' => $request->admission_number,
            'admission_year' => $request->admission_year,
            'date_of_admission' => $request->date_of_admission,
            'senior' => $request->senior,
            'stream' => $request->stream,
            'ple_score' => $request->ple_score,
            'uce_score' => $request->uce_score,
            'previous_school' => $request->previous_school,
            'primary_school_name' => $request->primary_school_name,
            'primary_contact' => $request->primary_contact,
            'other_contact' => $request->other_contact,
            'home_address' => $request->home_address,
            'guardian_names' => $request->guardian_names,
            'relation' => $request->relation,
            'guardian_phone' => $request->guardian_phone,
            'guardian_email' => $request->guardian_email,
            'medical_history' => $request->medical_history,
            'comments' => $request->comments,
        ]));

        return response()->json(['message' => 'Student updated successfully!']);
    }

    public function destroyStudent(Student $student)
    {
        try {
            if ($student->student_photo && file_exists(public_path($student->student_photo))) {
                unlink(public_path($student->student_photo));
            }

            $student->delete();

            return response()->json([
                'success' => true,
                'message' => 'Student deleted successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete student.',
            ], 500);
        }
    }

    public function exportStudents($schoolId, $type)
    {
        $activeYear = Helper::active_year();

        if ($activeYear == 'No Active year Set') {
            return back()->with('error', 'No Active Academic Year Set.');
        }

        $school = School::findOrFail($schoolId);

        $students = Student::where('school_id', $schoolId)->get();

        if ($type === 'thanawi') {
            $subjects = MasterData::where(
                'md_master_code_id',
                config('constants.options.ThanawiPapers')
            )->get();
        } else {
            $subjects = MasterData::where(
                'md_master_code_id',
                config('constants.options.IdaadPapers')
            )->get();
        }

        $cleanSchoolName = str_replace(' ', '_', $school->name);
        $cleanYear = str_replace(' ', '_', $activeYear);

        $fileName = $type.'_exams_'.$cleanYear.'_'.$cleanSchoolName.'.xlsx';

        return Excel::download(
            new StudentsExamExport($students, $subjects, $activeYear),
            $fileName
        );
    }

    public function uploadExamResults(Request $request)
    {
        $request->validate([
            'school_id' => 'required|exists:schools,id',
            'type' => 'required|in:thanawi,idaad',
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        $activeYear = Helper::active_year();
        if ($activeYear == 'No Active year Set') {
            return back()->with('error', 'No Active Academic Year Set.');
        }

        $subjects = $request->type === 'thanawi'
            ? MasterData::where('md_master_code_id', config('constants.options.ThanawiPapers'))->get()
            : MasterData::where('md_master_code_id', config('constants.options.IdaadPapers'))->get();

        if ($subjects->isEmpty()) {
            return back()->with('error', 'No subjects found for this exam type.');
        }

        // Create Exam record
        $exam = Exam::create([
            'school_id' => $request->school_id,
            'exam_type' => $request->type,
            'academic_year' => $activeYear,
        ]);

        // Import Excel
        Excel::import(new StudentExamImport($exam, $subjects), $request->file('file'));

        return back()->with('success', 'Exam results uploaded successfully.');
    }

    public function searchStudent()
    {
        Helper::requireSchool();

        // $classRecord = Helper::MasterRecordMerge(
        //     config('constants.options.O_LEVEL'),
        //     config('constants.options.A_LEVEL')
        // );
        $classRecord = Classroom::where('school_id', session('LoggedSchool'))->get();

        return view('student.student-search', compact(['classRecord']));
    }

    public function searchAjax(Request $request)
    {
        $criteria = $request->input('criteria');

        switch ($criteria) {
            case 'admission_number':
                $students = Student::where('admission_number', $request->admission_number)->get();
                break;
            case 'name':
                $students = Student::where('firstname', 'like', '%'.$request->firstname.'%')
                    ->where('lastname', 'like', '%'.$request->lastname.'%')
                    ->where('senior', $request->senior)
                    ->get();
                break;
            case 'phone':
                $students = Student::where('primary_contact', $request->phone)
                    ->orWhere('other_contact', $request->phone)
                    ->get();
                break;
            case 'student_id':
                $students = Student::where('id', $request->student_id)->get();
                break;
            default:
                return response()->json(['message' => 'Invalid criteria'], 400);
        }

        $html = view('student.partials.results', compact('students'))->render();

        return response()->json(['html' => $html]);
    }

    public function updateProfiles()
    {
        $students = Student::orderBy('created_at', 'desc')->get();

        $classRecord = Helper::MasterRecordMerge(
            config('constants.options.O_LEVEL'),
            config('constants.options.A_LEVEL')
        );

        $StreamRecord = Stream::where('school_id', Session('LoggedSchool'))->get();

        return view('student.student-information', compact(['students', 'classRecord', 'StreamRecord']));
    }

    public function update(Request $request, Student $student)
    {

        $validated = $request->validate([

            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'senior' => 'required|max:255',
            'stream' => 'required|max:255',
            'gender' => 'required|in:Male,Female,Other',
            'school_id' => 'required|integer|exists:schools,id',
            'admission_number' => 'nullable|string|max:255|unique:students,admission_number,'.$student->id,

        ]);

        $student->update($validated);

        return redirect()->route('students.index')->with('success', 'Student updated successfully!');
    }

    public function showStudentInformation($id)
    {
        $student = Student::findOrFail($id);

        return response()->json([
            'id' => $student->id,
            'firstname' => $student->firstname,
            'lastname' => $student->lastname,
            'gender' => $student->gender,
            'admission_number' => $student->admission_number,

            'senior_id' => $student->senior,
            'senior' => Helper::recordMdname($student->senior),

            'stream_id' => $student->stream,
            'stream' => Helper::recordMdname($student->stream),

            'primary_contact' => $student->primary_contact,
            'other_contact' => $student->other_contact,
            'date_of_birth' => $student->date_of_birth,
            'nationality' => $student->nationality,
            'guardian_names' => $student->guardian_names,
            'guardian_phone' => $student->guardian_phone,
        ]);
    }

    // public function updateStudentInformation(Request $request, $id)
    // {

    //     $validated = $request->validate([
    //         'firstname' => 'required|string|max:255',
    //         'lastname' => 'required|string|max:255',
    //         'senior' => 'nullable|max:255',
    //         'stream' => 'nullable|max:255',
    //         'gender' => 'required|in:Male,Female,Other',
    //         'school_id' => 'sometimes|integer|exists:schools,id',

    //         'admission_number' => 'nullable|string|max:255|unique:students,admission_number,'.$id,
    //         'primary_contact' => 'nullable|string|max:255',
    //         'other_contact' => 'nullable|string|max:255',
    //         'date_of_admission' => 'nullable|date',
    //         'date_of_birth' => 'nullable|date',
    //         'place_of_birth' => 'nullable|string|max:255',
    //         'nationality' => 'nullable|string|max:255',
    //         'ple_score' => 'nullable|numeric|between:0,999.99',
    //         'uce_score' => 'nullable|numeric|between:0,999.99',
    //         'previous_school' => 'nullable|string|max:255',
    //         'primary_school_name' => 'nullable|string|max:255',
    //         'guardian_names' => 'nullable|string|max:255',
    //         'relation' => 'nullable|string|max:255',
    //         'guardian_phone' => 'nullable|string|max:255',
    //         'guardian_email' => 'nullable|email|max:255',
    //         'home_address' => 'nullable|string',
    //         'birth_certificate_entry_number' => 'nullable|string|max:255',
    //         'medical_history' => 'nullable|string',
    //         'comments' => 'nullable|string',
    //     ]);

    //     $student = Student::findOrFail($id);
    //     $student->update($validated);

    //     return response()->json(['message' => 'Student updated successfully']);
    // }

    public function moveStudentForm()
    {
        $school_id = Session('LoggedSchool');

        $classrooms = Classroom::where('school_id', $school_id)->get();

        return view('student.move-student', compact('school_id', 'classrooms'));
    }

    private function calculateStatistics($results, $level = 'A')
    {
        $count = count($results);

        if ($count == 0) {
            return [
                'count' => 0,
                'average' => 0,
                'highest' => 0,
                'lowest' => 0,
                'grade_distribution' => [],
                'class_distribution' => [],
            ];
        }

        $percentages = array_column($results, 'percentage');

        // Get grade distribution
        $grades = Grading::marks($level)->get();
        $gradeDistribution = [];
        foreach ($grades as $grade) {
            $gradeDistribution[$grade->Grade] = 0;
        }

        $classDistribution = [];
        $classes = Grading::points($level)->get();
        foreach ($classes as $class) {
            $classDistribution[$class->Grade] = 0;
        }

        foreach ($results as $result) {
            if (isset($gradeDistribution[$result['grade']])) {
                $gradeDistribution[$result['grade']]++;
            }
            if (isset($classDistribution[$result['classification']])) {
                $classDistribution[$result['classification']]++;
            }
        }

        return [
            'count' => $count,
            'average' => round(array_sum($percentages) / $count, 2),
            'highest' => max($percentages),
            'lowest' => min($percentages),
            'grade_distribution' => $gradeDistribution,
            'class_distribution' => $classDistribution,
        ];
    }

    public function getStreamsByClass(Request $request)
    {
        $classId = $request->input('class_id');
        $streams = Stream::where('class_id', $classId)->get();

        // Map streams to include helper processed names
        $streams = $streams->map(function ($stream) {
            $stream->display_name = Helper::recordMdname($stream->stream_id);

            return $stream;
        });

        return response()->json($streams);
    }

    public function searchStudentsByClassStream(Request $request)
    {

        $validated = $request->validate([
            'school_id' => 'required|integer',
            'senior' => 'required|string',
            'stream' => 'required|string',
        ]);

        $students = Student::where('school_id', $validated['school_id'])
            ->where('senior', $validated['senior'])
            ->where('stream', $validated['stream'])
            ->select('id', 'firstname', 'lastname', 'admission_number')
            ->get();

        return response()->json($students);
    }

    public function moveStudent(Request $request)
    {

        $validated = $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'integer|exists:students,id',
            'new_senior' => 'required|string|max:255',
            'new_stream' => 'required|string|max:255',
        ]);

        DB::beginTransaction();

        try {

            Student::whereIn('id', $validated['student_ids'])->update([
                'senior' => $validated['new_senior'],
                'stream' => $validated['new_stream'],
            ]);

            DB::commit();

            return response()->json(['message' => 'Student(s) moved successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to move student(s).',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
