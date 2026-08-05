<?php

namespace App\Http\Controllers;

use App\Models\Examination;
use App\Models\ExaminationClass;
use App\Models\ParentAccount;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentFeeAllocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Self-service portal for parents/guardians. Identity here is a phone
 * number (ParentAccount), not tied to any one school — a parent with
 * children at different schools sees all of them from one login. Every
 * action below re-checks that the requested student's primary_contact
 * actually matches the logged-in parent's phone before showing anything,
 * since nothing in the schema formally links a parent row to a student.
 */
class ParentPortalController extends Controller
{
    // ─── Auth ───────────────────────────────────────────────────────────

    public function showLogin()
    {
        if (session('ParentId')) {
            return redirect()->route('parents.dashboard');
        }

        return view('parents.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        $phone = trim($request->input('phone'));

        $account = ParentAccount::findOrProvisionByPhone($phone);

        if (!$account) {
            return back()
                ->withInput()
                ->with('fail', 'We could not find a student record with that contact number. Please check with your school if you believe this is a mistake.');
        }

        if (!$account->checkPassword($request->input('password'))) {
            return back()->withInput()->with('fail', 'Incorrect password.');
        }

        $account->update(['last_login_at' => now()]);

        session([
            'ParentId' => $account->id,
            'ParentPhone' => $account->phone,
        ]);

        if ($account->must_change_password) {
            return redirect()->route('parents.change-password')
                ->with('info', 'Welcome! Please set your own password to continue.');
        }

        $intended = session()->pull('url.intended');

        return $intended ? redirect($intended) : redirect()->route('parents.dashboard');
    }

    public function logout()
    {
        session()->forget(['ParentId', 'ParentPhone', 'ParentActiveStudent']);
        return redirect()->route('parents.login')->with('info', 'You have been logged out.');
    }

    public function showChangePassword()
    {
        return view('parents.change-password');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:4|confirmed',
        ]);

        $account = ParentAccount::findOrFail(session('ParentId'));

        $account->update([
            'password' => Hash::make($request->input('password')),
            'must_change_password' => false,
        ]);

        return redirect()->route('parents.dashboard')->with('success', 'Password updated. Welcome to your parent portal.');
    }

    // ─── Dashboard / child switcher ────────────────────────────────────

    public function dashboard()
    {
        $children = $this->myChildren();

        return view('parents.dashboard', compact('children'));
    }

    public function childOverview($studentId)
    {
        $student = $this->authorizedStudent($studentId);

        $attendanceSummary = StudentAttendance::where('student_id', $student->id)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $feeSummary = StudentFeeAllocation::where('student_id', $student->id)
            ->selectRaw('sum(allocated_amount - discount_amount) as charged, sum(balance) as owing')
            ->first();

        $releasedResultsCount = $this->releasedExamsFor($student)->count();

        return view('parents.child-overview', compact(
            'student',
            'attendanceSummary',
            'feeSummary',
            'releasedResultsCount'
        ));
    }

    // ─── Results ────────────────────────────────────────────────────────

    public function results($studentId)
    {
        $student = $this->authorizedStudent($studentId);

        $exams = $this->releasedExamsFor($student);

        return view('parents.results', compact('student', 'exams'));
    }

    public function viewResult($studentId, $examId)
    {
        $student = $this->authorizedStudent($studentId);

        $exam = Examination::where('id', $examId)
            ->where('school_id', $student->school_id)
            ->firstOrFail();

        /** @var ExaminationController $examController */
        $examController = app(ExaminationController::class);

        if (!$examController->classIsReleased($exam, $student->senior, $student->stream, $student->school_id)) {
            abort(403, 'Results for this exam have not been released yet.');
        }

        $examController->applySavedPassslipSettings($student->school_id, $student->senior);

        [$examIds, $avgExamIds] = $examController->resolveExamSelection($examId);
        $multiExam = count($examIds) > 1;

        $passslipData = $multiExam
            ? $examController->buildMultiExamPassslipData($examIds, $studentId, $student->school_id, $avgExamIds, $student)
            : $examController->buildPassslipData($examId, $studentId, $student->school_id, $exam, $student);

        $qrText = $passslipData['qrText'] ?? '';
        $subjectMarks = $passslipData['subjectMarks'] ?? collect();
        $totalObtained = $passslipData['totalObtained'] ?? 0;
        $totalMax = $passslipData['totalMax'] ?? 0;
        $percentage = $passslipData['percentage'] ?? 0;
        $overallGrade = $passslipData['overallGrade'] ?? '—';
        $overallRemark = $passslipData['overallRemark'] ?? '—';
        $classRank = $passslipData['classRank'] ?? '—';
        $classTotal = $passslipData['classTotal'] ?? 0;
        $growthData = $passslipData['growthData'] ?? [];
        $previousSubjectMarks = $passslipData['previousSubjectMarks'] ?? collect();
        $isEarlyYears = $passslipData['isEarlyYears'] ?? false;
        $earlyYearsAverage = $passslipData['earlyYearsAverage'] ?? null;
        $earlyYearsMaxMark = $passslipData['earlyYearsMaxMark'] ?? Helper::earlyYearsMaxMark();
        $examsList = $passslipData['examsList'] ?? collect([$exam]);
        $useAvg = $passslipData['useAvg'] ?? false;
        $examSummary = $passslipData['examSummary'] ?? [];
        $avgSummary = $passslipData['avgSummary'] ?? null;

        $isNursery = $examController->isNurseryClass($student->senior);
        $view = $isNursery ? 'Examination.passslips.slip-nursery' : 'Examination.passslips.slip';

        return view($view, compact(
            'exam',
            'student',
            'qrText',
            'subjectMarks',
            'totalObtained',
            'totalMax',
            'percentage',
            'overallGrade',
            'overallRemark',
            'classRank',
            'classTotal',
            'growthData',
            'previousSubjectMarks',
            'isEarlyYears',
            'earlyYearsAverage',
            'earlyYearsMaxMark',
            'examsList',
            'useAvg',
            'examSummary',
            'avgSummary'
        ) + ['mode' => 'single', 'multiExam' => $multiExam, 'parentView' => true]);
    }

    // ─── Attendance ─────────────────────────────────────────────────────

    public function attendance(Request $request, $studentId)
    {
        $student = $this->authorizedStudent($studentId);

        $month = $request->input('month', now()->format('Y-m'));

        $records = StudentAttendance::where('student_id', $student->id)
            ->whereRaw("DATE_FORMAT(attendance_date, '%Y-%m') = ?", [$month])
            ->orderByDesc('attendance_date')
            ->get();

        $summary = $records->countBy('status');

        $totalDays = $records->count();
        $presentDays = $summary->get('present', 0) + $summary->get('late', 0);
        $attendanceRate = $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 1) : null;

        $availableMonths = StudentAttendance::where('student_id', $student->id)
            ->selectRaw("DISTINCT DATE_FORMAT(attendance_date, '%Y-%m') as ym")
            ->orderByDesc('ym')
            ->pluck('ym');

        return view('parents.attendance', compact(
            'student',
            'records',
            'summary',
            'attendanceRate',
            'month',
            'availableMonths'
        ));
    }

    // ─── Finance ────────────────────────────────────────────────────────

    public function finance(Request $request, $studentId)
    {
        $student = $this->authorizedStudent($studentId);

        $year = $request->input('year', date('Y'));

        $allocations = StudentFeeAllocation::where('student_id', $student->id)
            ->when($year, fn($q) => $q->where('academic_year', $year))
            ->with(['feeStructure', 'payments' => fn($q) => $q->orderBy('payment_date')])
            ->orderBy('academic_year')
            ->orderBy('term')
            ->get();

        $statement = collect();
        $runningBalance = 0;

        foreach ($allocations as $allocation) {
            $charge = (float) $allocation->allocated_amount - (float) $allocation->discount_amount;
            $runningBalance += $charge;

            $statement->push([
                'date' => $allocation->created_at,
                'type' => 'charge',
                'description' => 'Fee charge — ' . ($allocation->feeStructure->name ?? 'Fee Structure')
                    . " (Term {$allocation->term}, {$allocation->academic_year})"
                    . ($allocation->discount_amount > 0 ? ' — discount applied' : ''),
                'debit' => $charge,
                'credit' => null,
                'balance' => $runningBalance,
            ]);

            foreach ($allocation->payments as $payment) {
                if ($payment->status !== 'confirmed') {
                    continue;
                }
                $runningBalance -= (float) $payment->amount_paid;

                $statement->push([
                    'date' => $payment->payment_date,
                    'type' => 'payment',
                    'description' => 'Payment received — Receipt ' . $payment->receipt_number . ' (' . $payment->methodLabel() . ')',
                    'debit' => null,
                    'credit' => (float) $payment->amount_paid,
                    'balance' => $runningBalance,
                ]);
            }
        }

        $totalCharges = $allocations->sum(fn($a) => $a->allocated_amount - $a->discount_amount);
        $totalPaid = $allocations->flatMap->payments->where('status', 'confirmed')->sum('amount_paid');
        $arrears = $totalCharges - $totalPaid;

        $availableYears = StudentFeeAllocation::where('student_id', $student->id)
            ->distinct()
            ->orderByDesc('academic_year')
            ->pluck('academic_year');

        return view('parents.finance', compact(
            'student', 'statement', 'totalCharges', 'totalPaid', 'arrears', 'year', 'availableYears'
        ));
    }

    // ─── Shared helpers ─────────────────────────────────────────────────

    private function myChildren()
    {
        $phone = session('ParentPhone');

        return Student::where('primary_contact', $phone)
            ->get()
            ->map(function ($student) {
                $student->school_name = Helper::schoolNameBySchoolID($student->school_id);
                $student->class_name = Helper::recordMdname($student->senior);
                $student->stream_name = Helper::recordMdname($student->stream);
                return $student;
            })
            ->groupBy('school_id');
    }

    private function authorizedStudent($studentId): Student
    {
        $phone = session('ParentPhone');
        $student = Student::where('id', $studentId)->where('primary_contact', $phone)->first();

        if (!$student) {
            abort(403, "That student isn't linked to your account.");
        }

        $student->school_name = Helper::schoolNameBySchoolID($student->school_id);
        $student->class_name = Helper::recordMdname($student->senior);
        $student->stream_name = Helper::recordMdname($student->stream);

        return $student;
    }

    /**
     * Every released exam for this student's class/stream, newest first —
     * "released" checked per-class first (a class can be released ahead
     * of the rest of the exam), falling back to exam-level status.
     */
    private function releasedExamsFor(Student $student)
    {
        /** @var ExaminationController $examController */
        $examController = app(ExaminationController::class);

        return Examination::where('school_id', $student->school_id)
            ->whereHas('examinationClasses', function ($q) use ($student) {
                $q->where('class_id', $student->senior)->where('stream_id', $student->stream);
            })
            ->orderByDesc('start_date')
            ->get()
            ->filter(fn($exam) => $examController->classIsReleased($exam, $student->senior, $student->stream, $student->school_id))
            ->values();
    }
}
