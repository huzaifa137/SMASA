<?php

namespace App\Http\Controllers;

use App\Support\SchoolRule;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StudentExamImport;
use App\Models\AnnualExamination;
use App\Models\Exam;
use App\Models\School;
use App\Models\MasterData;
use Illuminate\Http\Request;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Services\GradingService;
use Illuminate\Support\Facades\DB;

class GradingController extends Controller
{
    public function importMarks()
    {
        $students = Student::with('school')
            ->fromSub(function ($query) {
                $query->from('students')
                    ->select('*')
                    ->selectRaw(
                        'ROW_NUMBER() OVER (
                    PARTITION BY school_id
                    ORDER BY id
                ) as row_num'
                    );
            }, 'students')
            ->where('row_num', '<=', 1)
            ->orderBy('school_id')
            ->orderBy('id')
            ->get();

        $groupedStudents = $students->groupBy(function ($student) {
            return $student->school->name;
        });

        $thanawiPapers = MasterData::where(
            'md_master_code_id',
            config('constants.options.ThanawiPapers')
        )->get();

        $idaadPapers = MasterData::where(
            'md_master_code_id',
            config('constants.options.IdaadPapers')
        )->get();


        return view('Grading.import-marks', compact(
            'groupedStudents',
            'thanawiPapers',
            'idaadPapers'
        ));
    }

    public function createExamination()
    {
        // Admin
        $activeYear = AcademicYear::orderBy('id', 'desc')
            ->where('is_active', 1)
            ->value('name');

        $schools = MasterData::where('md_master_code_id', config('constants.options.SCHOOL_TERMS'))->get();

        return view(
            'Grading.create-examination',
            compact('activeYear')
        );
    }

    public function storeCreatedExamination(Request $request)
    {
        // Annual (national) examinations are platform-wide: platform admins only.
        abort_unless(session()->has('LoggedAdmin'), 403);


        $validator = \Validator::make($request->all(), [
            'ExaminationName' => 'required|string',
            'year' => 'required|integer',
            'marks_upload_enabled' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $examination_name = ($request->ExaminationName === "194") ? "Idaad" : "Thanawi";

        $existingExam = AnnualExamination::where('year', $request->year)
            ->where('examination_name', $examination_name)
            ->first();

        if ($existingExam) {
            return response()->json([
                'errors' => ['year' => ["An examination with name '{$examination_name}' for this year already exists."]]
            ], 422);
        }

        $activeExam = AnnualExamination::where('year', $request->year)
            ->where('examination_name', $examination_name)
            ->where('is_active', true)
            ->first();

        if ($activeExam) {
            return response()->json([
                'errors' => ['is_active' => ["There is an existing active examination '{$examination_name}' for this year. Please disable it first."]]
            ], 422);
        }

        $oldActiveExam = AnnualExamination::where('year', '!=', $request->year)
            ->where('examination_name', $examination_name)
            ->where('is_active', true)
            ->first();

        if ($oldActiveExam) {
            return response()->json([
                'errors' => ['is_active' => ["There is an existing old active examination '{$examination_name}' for the '{$oldActiveExam->year}'. Please disable this year '{$oldActiveExam->year}' first."]]
            ], 422);
        }

        AnnualExamination::create([
            'examination_name' => $examination_name,
            'examination_classification' => $examination_name,
            'year' => $request->year,
            'is_active' => (bool) $request->marks_upload_enabled,
        ]);

        return response()->json(['success' => true, 'message' => 'Examination created successfully.']);
    }

    public function toggleExamActive(Request $request)
    {
        // Annual (national) examinations are platform-wide: platform admins only.
        abort_unless(session()->has('LoggedAdmin'), 403);

        $request->validate([
            'exam_id' => 'required|exists:annual_examinations,id',
            'is_active' => 'required|boolean',
        ]);

        $exam = AnnualExamination::findOrFail($request->exam_id);

        if ($request->is_active) {
            AnnualExamination::where('examination_name', $exam->examination_name)
                ->where('id', '!=', $exam->id)
                ->update(['is_active' => false]);
        }

        $exam->is_active = $request->is_active;
        $exam->save();

        return response()->json([
            'success' => true,
            'message' => $request->is_active
                ? "Exam '{$exam->examination_name}' activated."
                : "Exam '{$exam->examination_name}' deactivated."
        ]);
    }

    public function getExamYears()
    {
        $years = AnnualExamination::select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        return response()->json($years);
    }

    public function getExamsByYear($year)
    {
        $exams = AnnualExamination::where('year', $year)
            ->orderBy('examination_name', 'asc')
            ->get();

        return response()->json($exams);
    }

    public function getActiveExams()
    {
        $activeExams = AnnualExamination::where('is_active', true)
            ->orderBy('examination_name')
            ->get();

        return response()->json($activeExams);
    }

    public function importIdaadResults(Request $request)
    {
        return $this->handleExamImport($request, 'idaad');
    }

    public function importThanawiResults(Request $request)
    {
        return $this->handleExamImport($request, 'thanawi');
    }

    private function handleExamImport(Request $request, $examType)
    {
        $request->validate([
            'school_id' => ['required', SchoolRule::school()],
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        $activeYear = Helper::active_year();
        if ($activeYear == 'No Active year Set') {
            return back()->with('error', 'No Active Academic Year Set.');
        }

        $uploadingYear = $examType === 'thanawi'
            ? Helper::activeUploadingThanawiYear()
            : Helper::activeUploadingIdaadYear();

        if ($uploadingYear === 'Upload Year Not Set') {
            return back()->with('error', ucfirst($examType) . ' upload is not currently active.');
        }

        if ($uploadingYear !== $activeYear) {
            return back()->with(
                'error',
                ucfirst($examType) . " upload is active for {$uploadingYear}, but academic year is {$activeYear}."
            );
        }

        $subjects = $examType === 'thanawi'
            ? MasterData::where('md_master_code_id', config('constants.options.ThanawiPapers'))->get()
            : MasterData::where('md_master_code_id', config('constants.options.IdaadPapers'))->get();

        if ($subjects->isEmpty()) {
            return back()->with('error', 'No subjects found for this exam type.');
        }

        DB::beginTransaction();

        try {

            // 🔎 Check existing exam
            $existingExam = Exam::where('school_id', $request->school_id)
                ->where('exam_type', $examType)
                ->where('academic_year', $activeYear)
                ->first();

            if ($existingExam) {
                app(GradingService::class)->forgetExamSummary($existingExam->getKey());
                $existingExam->delete();
            }

            // ✅ Create fresh exam
            $exam = Exam::create([
                'school_id' => $request->school_id,
                'exam_type' => $examType,
                'academic_year' => $activeYear
            ]);

            Excel::import(new StudentExamImport($exam, $subjects), $request->file('file'));

            // Compute the ranking rows once, now that the marks are saved.
            app(GradingService::class)->refreshExamSummary($exam->getKey());

            DB::commit();

            return back()->with('success', ucfirst($examType) . ' results uploaded successfully.');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }
    }

    public function showExamResults($examId, GradingService $gradingService)
    {
        // School staff may only open their own school's exam; anything else is
        // a 404 (never confirm another school's exam exists).
        if (!session()->has('LoggedAdmin')) {
            $schoolId = session('LoggedSchool');
            abort_unless($schoolId, 403);

            $owner = \Illuminate\Support\Facades\Schema::hasColumn('exams', 'school_id')
                ? DB::table('exams')->where('id', $examId)->value('school_id')
                : null;

            abort_unless($owner !== null && (int) $owner === (int) $schoolId, 404);
        }


        $results = $gradingService->calculateExamResults($examId);

        $results = $results->map(function ($result) {

            $student = $result->student;

            $result->student_name = $student->firstname . ' ' . $student->lastname;
            $result->admission_number = $student->admission_number;
            $result->gender = $student->gender;
            $result->stream = $student->stream;
            $result->school_name = $student->school->name ?? 'N/A';

            return $result;
        });

        return view('Grading.grade-summary', compact('results'));
    }

    public function gradingDashboard(Request $request, GradingService $gradingService)
    {
        // Filters
        $examType = $request->exam_type;
        $academicYear = $request->academic_year;
        $schoolId = $request->school_id;

        // School staff may only ever see their own school's results. Only a
        // platform admin can filter on (or leave open) other schools.
        $isPlatformAdmin = session()->has('LoggedAdmin');
        if (!$isPlatformAdmin) {
            $schoolId = session('LoggedSchool');
            abort_unless($schoolId, 403);
        }

        // Ranking comes from the precomputed exam_result_summaries table:
        // the database sorts and counts, PHP only builds the 10 rows shown.
        $filters = [
            'exam_type' => $examType,
            'academic_year' => $academicYear,
            'school_id' => $schoolId,
        ];

        $gradingUnavailable = !$gradingService->summariesAvailable();

        if (!$gradingUnavailable) {
            $gradingService->ensureSummaries($filters);
        }

        $results = $gradingUnavailable
            ? new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10, 1, ['path' => request()->url()])
            : $gradingService->rankedPage(
                $filters,
                max(1, (int) request()->query('page', 1)),
                10,
                request()->url(),
                request()->query()
            );

        // Subject statistics
        $subjectStats = null;
        // Subject statistics are computed across all schools: platform admin only.
        if ($isPlatformAdmin && $examType && $academicYear && !$gradingUnavailable) {
            try {
                $subjectStats = $gradingService->subjectStatistics($examType, $academicYear);
            } catch (\Throwable $e) {
                // StudentExamResult's subject/exam relations are commented out in the
                // model, so this can fail; the ranking itself must still render.
                report($e);
            }
        }

        // Only the school currently filtered on is rendered; the picker searches
        // the rest through admin.schools.lookup.
        $schools = $schoolId
            ? School::whereKey((int) $schoolId)->get(['id', 'name'])
            : collect();
        $years = $gradingUnavailable ? collect() : DB::table('exams')->whereNotNull('academic_year')->distinct()->pluck('academic_year');

        return view('Grading.dashboard', compact(
            'gradingUnavailable',
            'results',
            'schools',
            'years',
            'subjectStats'
        ));
    }
}
